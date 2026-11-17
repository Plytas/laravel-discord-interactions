<?php

namespace Plytas\Discord\RateLimiting;

use Closure;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Plytas\Discord\Exceptions\DiscordRateLimitedException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Guzzle middleware that follows the Discord rate limit headers.
 *
 * Every response tells which bucket its route uses, how many requests the bucket has left, and when it resets. The
 * limiter keeps this in the cache, so that all workers share it. Before a request goes out, the limiter takes one
 * request from the bucket. When the bucket or the global limit is empty, the request waits until the reset with the
 * non-blocking Guzzle delay option, or throws DiscordRateLimitedException when the wait is longer than max_wait.
 *
 * @see https://discord.com/developers/docs/topics/rate-limits
 */
final readonly class DiscordRateLimiter
{
    private const PREFIX = 'discord-interactions:rate-limit:';

    private const ROUTE_BUCKET_TTL = 86400;

    public function __construct(
        private Repository $cache,
        private float $maxWait,
    ) {}

    /**
     * Returns null when the rate_limit.enabled config is false.
     */
    public static function fromConfig(): ?self
    {
        if (config('discord-interactions.rate_limit.enabled', true) === false) {
            return null;
        }

        $store = config('discord-interactions.rate_limit.cache_store');
        $maxWait = config('discord-interactions.rate_limit.max_wait', 2);

        return new self(
            cache: Cache::store(is_string($store) ? $store : null),
            maxWait: is_numeric($maxWait) ? (float) $maxWait : 2.0,
        );
    }

    /**
     * @param  callable(RequestInterface, array<string, mixed>): PromiseInterface  $handler
     * @return Closure(RequestInterface, array<string, mixed>): PromiseInterface
     */
    public function __invoke(callable $handler): Closure
    {
        return function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
            $route = DiscordRoute::fromRequest($request);

            try {
                $waitSeconds = $this->reserve($route);
            } catch (DiscordRateLimitedException $exception) {
                return Create::rejectionFor($exception);
            }

            if ($waitSeconds > 0) {
                $currentDelay = $options['delay'] ?? 0;
                $options['delay'] = max(is_numeric($currentDelay) ? (float) $currentDelay : 0.0, ceil($waitSeconds * 1000));
            }

            return $handler($request, $options)->then(function (ResponseInterface $response) use ($route): ResponseInterface {
                $this->record($route, new Response($response));

                return $response;
            });
        };
    }

    /**
     * A 429 response is retried once when Discord asks to wait no longer than max_wait.
     */
    public function shouldRetry(mixed $exception): bool
    {
        $retryAfter = $this->retryAfter($exception);

        return $retryAfter !== null && $retryAfter <= $this->maxWait;
    }

    public function retryDelayInMilliseconds(mixed $exception): int
    {
        return (int) ceil(($this->retryAfter($exception) ?? 0) * 1000);
    }

    /**
     * Takes one request from the bucket of the route and returns how long, in seconds, the request must wait.
     *
     * @throws DiscordRateLimitedException
     */
    private function reserve(DiscordRoute $route): float
    {
        $now = $this->now();
        $waitSeconds = 0.0;

        $globalResetAt = $this->cache->get($this->globalKey());

        if (is_numeric($globalResetAt) && $globalResetAt > $now) {
            $waitSeconds = $globalResetAt - $now;
        }

        $bucket = $this->cache->get($this->routeKey($route));

        if (is_string($bucket)) {
            $bucketKey = $this->bucketKey($bucket, $route);
            $resetAt = $this->cache->get("{$bucketKey}:reset");

            if (is_numeric($resetAt) && $resetAt > $now) {
                $remaining = $this->cache->decrement("{$bucketKey}:remaining");

                if (is_int($remaining) && $remaining < 0) {
                    $waitSeconds = max($waitSeconds, $resetAt - $now);
                }
            }
        }

        if ($waitSeconds > $this->maxWait) {
            throw new DiscordRateLimitedException($route->template, $waitSeconds);
        }

        return $waitSeconds;
    }

    private function record(DiscordRoute $route, Response $response): void
    {
        $now = $this->now();
        $bucket = $response->header('X-RateLimit-Bucket');

        if ($bucket !== '') {
            $this->cache->put($this->routeKey($route), $bucket, self::ROUTE_BUCKET_TTL);

            $remaining = $response->header('X-RateLimit-Remaining');
            $resetAfter = $response->header('X-RateLimit-Reset-After');

            if (is_numeric($remaining) && is_numeric($resetAfter)) {
                $this->putBucket($this->bucketKey($bucket, $route), (int) $remaining, $now + (float) $resetAfter, (float) $resetAfter);
            }
        }

        $retryAfter = $this->retryAfterSeconds($response);

        if ($retryAfter === null) {
            return;
        }

        if ($response->header('X-RateLimit-Global') === 'true' || $response->json('global') === true) {
            $this->cache->put($this->globalKey(), $now + $retryAfter, $this->ttl($retryAfter));
        } elseif ($bucket !== '') {
            $this->putBucket($this->bucketKey($bucket, $route), 0, $now + $retryAfter, $retryAfter);
        }
    }

    private function putBucket(string $bucketKey, int $remaining, float $resetAt, float $resetAfter): void
    {
        $ttl = $this->ttl($resetAfter);

        $this->cache->put("{$bucketKey}:reset", $resetAt, $ttl);
        $this->cache->put("{$bucketKey}:remaining", $remaining, $ttl);
    }

    private function retryAfter(mixed $exception): ?float
    {
        return $exception instanceof RequestException ? $this->retryAfterSeconds($exception->response) : null;
    }

    /**
     * The seconds that a 429 response asks to wait, or null for any other response.
     */
    private function retryAfterSeconds(Response $response): ?float
    {
        $retryAfter = $response->status() === 429 ? $response->json('retry_after') : null;

        return is_numeric($retryAfter) ? (float) $retryAfter : null;
    }

    private function routeKey(DiscordRoute $route): string
    {
        return self::PREFIX.'route:'.md5($route->template);
    }

    private function bucketKey(string $bucket, DiscordRoute $route): string
    {
        return self::PREFIX.'bucket:'.$bucket.':'.md5($route->majorParameter);
    }

    private function globalKey(): string
    {
        return self::PREFIX.'global';
    }

    private function ttl(float $seconds): int
    {
        return max(1, (int) ceil($seconds));
    }

    private function now(): float
    {
        return Carbon::now()->getPreciseTimestamp() / 1_000_000;
    }
}
