<?php

use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Plytas\Discord\DiscordPool;
use Plytas\Discord\Exceptions\DiscordRateLimitedException;
use Plytas\Discord\Facades\Discord;
use Plytas\Discord\RateLimiting\DiscordRoute;

beforeEach(function () {
    config([
        'app.url' => 'https://example.com',
        'discord-interactions.bot_token' => 'token',
        'discord-interactions.rate_limit.max_wait' => 2,
    ]);

    Sleep::fake();
});

function rateLimitHeaders(string $bucket, int $remaining, float $resetAfter): array
{
    return [
        'X-RateLimit-Bucket' => $bucket,
        'X-RateLimit-Remaining' => (string) $remaining,
        'X-RateLimit-Reset-After' => (string) $resetAfter,
    ];
}

it('groups routes by template and major parameter', function (string $method, string $path, string $template, string $major) {
    $route = DiscordRoute::fromRequest(new Request($method, "https://discord.com/api/v10{$path}"));

    expect($route->template)->toBe($template)
        ->and($route->majorParameter)->toBe($major);
})->with([
    'channel message' => ['PATCH', '/channels/111/messages/222', 'PATCH /channels/{major}/messages/{id}', 'channels/111'],
    'guild channels' => ['GET', '/guilds/333/channels', 'GET /guilds/{major}/channels', 'guilds/333'],
    'interaction webhook' => ['PATCH', '/webhooks/444/tok-en/messages/@original', 'PATCH /webhooks/{major}/{major}/messages/@original', 'webhooks/444/tok-en'],
    'user' => ['GET', '/users/555', 'GET /users/{id}', ''],
]);

it('does not send a request while its bucket is empty for longer than max_wait', function () {
    Http::fake([
        'discord.com/api/v10/channels/1/messages/*' => Http::response([], 204, rateLimitHeaders('bucket-a', 0, 30)),
    ]);

    Discord::deleteMessage('1', '10');

    expect(fn () => Discord::deleteMessage('1', '11'))->toThrow(DiscordRateLimitedException::class);
    Http::assertSentCount(1);
});

it('sends the request when the bucket resets within max_wait', function () {
    Http::fake([
        'discord.com/api/v10/channels/1/messages/*' => Http::response([], 204, rateLimitHeaders('bucket-a', 0, 1)),
    ]);

    Discord::deleteMessage('1', '10');
    Discord::deleteMessage('1', '11');

    Http::assertSentCount(2);
});

it('keeps the buckets of different major parameters apart', function () {
    Http::fake([
        'discord.com/api/v10/channels/*' => Http::response([], 204, rateLimitHeaders('bucket-a', 0, 30)),
    ]);

    Discord::deleteMessage('1', '10');
    Discord::deleteMessage('2', '10');

    Http::assertSentCount(2);
});

it('sends the request again after the bucket resets', function () {
    Http::fake([
        'discord.com/api/v10/channels/1/messages/*' => Http::response([], 204, rateLimitHeaders('bucket-a', 0, 30)),
    ]);

    Discord::deleteMessage('1', '10');
    $this->travel(31)->seconds();
    Discord::deleteMessage('1', '11');

    Http::assertSentCount(2);
});

it('retries a 429 once when retry_after is within max_wait', function () {
    Http::fake([
        'discord.com/api/v10/guilds/1/roles' => Http::sequence()
            ->push(['message' => 'You are being rate limited.', 'retry_after' => 0.5, 'global' => false], 429)
            ->push([['id' => '7', 'name' => 'Raiders', 'description' => null, 'color' => 0]]),
    ]);

    expect(Discord::getRoles('1')->sole()->name)->toBe('Raiders');
    Http::assertSentCount(2);
});

it('does not retry a 429 when retry_after is longer than max_wait', function () {
    Http::fake([
        'discord.com/api/v10/guilds/1/roles' => Http::response(['message' => 'You are being rate limited.', 'retry_after' => 30, 'global' => false], 429, ['X-RateLimit-Bucket' => 'bucket-r']),
    ]);

    expect(fn () => Discord::getRoles('1'))->toThrow(RequestException::class)
        ->and(fn () => Discord::getRoles('1'))->toThrow(DiscordRateLimitedException::class);
    Http::assertSentCount(1);
});

it('stops every route after a global 429', function () {
    Http::fake([
        'discord.com/api/v10/guilds/1/roles' => Http::response(['message' => 'You are being rate limited.', 'retry_after' => 30, 'global' => true], 429, ['X-RateLimit-Global' => 'true']),
    ]);

    expect(fn () => Discord::getRoles('1'))->toThrow(RequestException::class)
        ->and(fn () => Discord::deleteMessage('9', '10'))->toThrow(DiscordRateLimitedException::class);
    Http::assertSentCount(1);
});

it('applies the rate limit to pooled requests', function () {
    Http::fake([
        'discord.com/api/v10/guilds/1/roles' => Http::response([], 200, rateLimitHeaders('bucket-r', 0, 30)),
        'discord.com/api/v10/guilds/1/channels' => Http::response([]),
    ]);

    Discord::getRoles('1');

    expect(fn () => Discord::pool(fn (DiscordPool $pool) => [$pool->getChannels('1'), $pool->getRoles('1')]))
        ->toThrow(DiscordRateLimitedException::class);
});

it('sends every request when the rate limiter is turned off', function () {
    config(['discord-interactions.rate_limit.enabled' => false]);

    Http::fake([
        'discord.com/api/v10/channels/1/messages/*' => Http::response([], 204, rateLimitHeaders('bucket-a', 0, 30)),
    ]);

    Discord::deleteMessage('1', '10');
    Discord::deleteMessage('1', '11');

    Http::assertSentCount(2);
});
