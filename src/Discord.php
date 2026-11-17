<?php

namespace Plytas\Discord;

use Closure;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Plytas\Discord\Data\DiscordApplicationCommand;
use Plytas\Discord\Data\DiscordChannel;
use Plytas\Discord\Data\DiscordGuild;
use Plytas\Discord\Data\DiscordInteraction;
use Plytas\Discord\Data\DiscordMessage;
use Plytas\Discord\Data\DiscordRole;
use Plytas\Discord\Data\DiscordUser;
use Plytas\Discord\Exceptions\InvalidConfigurationException;
use Plytas\Discord\RateLimiting\DiscordRateLimiter;
use Plytas\Discord\Requests\DiscordReadRequest;
use Throwable;

readonly class Discord
{
    private const BASE_URL = 'https://discord.com/api/v10';

    private string $userAgent;

    private string $botToken;

    private DiscordRateLimiter $rateLimiter;

    /**
     * @throws InvalidConfigurationException
     */
    public function __construct()
    {
        $appUrl = config('app.url');
        $botToken = config('discord-interactions.bot_token');

        if (! is_string($appUrl)) {
            throw new InvalidConfigurationException('app.url');
        }

        if (! is_string($botToken)) {
            throw new InvalidConfigurationException('discord-interactions.bot_token');
        }

        $this->userAgent = "DiscordBot ($appUrl, 1)";
        $this->botToken = $botToken;
        $this->rateLimiter = DiscordRateLimiter::fromConfig();
    }

    public function createCommand(DiscordApplicationCommand $command): Response
    {
        return $this->request()->post("/applications/{$this->applicationId()}/commands", $command->toArray());
    }

    /**
     * @return Collection<int, DiscordApplicationCommand>
     */
    public function getCommands(): Collection
    {
        return $this->read(DiscordReadRequest::commands($this->applicationId()));
    }

    public function deleteCommand(string $commandId): Response
    {
        return $this->request()->throw()->delete("/applications/{$this->applicationId()}/commands/{$commandId}");
    }

    public function updateInteractionMessage(DiscordInteraction $interaction, DiscordMessage $message): Response
    {
        return $this->request()->patch("/webhooks/{$interaction->application_id}/{$interaction->token}/messages/@original", $message->toArray());
    }

    public function deleteInteractionMessage(DiscordInteraction $interaction): Response
    {
        return $this->request()->delete("/webhooks/{$interaction->application_id}/{$interaction->token}/messages/@original");
    }

    public function createMessage(string $channelId, DiscordMessage $message): Response
    {
        return $this->withMessage($message)->post("/channels/{$channelId}/messages", $this->messageData($message));
    }

    public function updateMessage(string $channelId, string $messageId, DiscordMessage $message): Response
    {
        return $this->withMessage($message)->patch("/channels/{$channelId}/messages/{$messageId}", $this->messageData($message));
    }

    public function deleteMessage(string $channelId, string $messageId): Response
    {
        return $this->request()->delete("/channels/{$channelId}/messages/{$messageId}");
    }

    public function openDirectMessageChannel(string $userId): Response
    {
        return $this->request()->asJson()->post('/users/@me/channels', ['recipient_id' => $userId]);
    }

    public function getUser(string $userId): DiscordUser
    {
        return $this->read(DiscordReadRequest::user($userId));
    }

    public function getGuild(string $guildId): DiscordGuild
    {
        return $this->read(DiscordReadRequest::guild($guildId));
    }

    /**
     * Channels with a type that ChannelType does not know are skipped, because Discord adds new channel types over time.
     *
     * @return Collection<int, DiscordChannel>
     */
    public function getChannels(string $guildId): Collection
    {
        return $this->read(DiscordReadRequest::channels($guildId));
    }

    /**
     * @return Collection<int, DiscordRole>
     */
    public function getRoles(string $guildId): Collection
    {
        return $this->read(DiscordReadRequest::roles($guildId));
    }

    /**
     * Sends read requests at the same time and returns their results with the keys of the callback's array.
     *
     *     [$channels, $roles] = Discord::pool(fn (DiscordPool $pool) => [
     *         $pool->getChannels($guildId),
     *         $pool->getRoles($guildId),
     *     ]);
     *
     * The rate limiter applies to each request in the pool. A failed request throws, the same as the method with
     * the same name on Discord.
     *
     * @template TKey of array-key
     *
     * @param  Closure(DiscordPool): array<TKey, DiscordReadRequest<mixed>>  $callback
     * @param  positive-int|null  $concurrency  The number of requests sent at the same time. Null uses the pool_concurrency config.
     * @return array<TKey, mixed>
     */
    public function pool(Closure $callback, ?int $concurrency = null): array
    {
        $discordPool = new DiscordPool($this->optionalApplicationId());
        $results = $callback($discordPool);
        $requests = $discordPool->getRequests();

        $responses = Http::pool(function (Pool $pool) use ($requests): void {
            foreach ($requests as $index => $request) {
                $this->configure($pool->as((string) $index))->throw()->get($request->path);
            }
        }, $concurrency ?? $this->poolConcurrency());

        return array_map(static function (DiscordReadRequest $request) use ($requests, $responses): mixed {
            $index = array_search($request, $requests, true);

            if ($index === false) {
                throw new InvalidArgumentException('The pool callback must return only requests from its DiscordPool.');
            }

            $response = $responses[$index];

            if ($response instanceof Throwable) {
                throw $response;
            }

            return $request->parse($response->throw());
        }, $results);
    }

    /**
     * A new request for each call, because PendingRequest methods such as throw() and attach() change the request
     * they are called on. One shared request would keep these settings for all later calls.
     */
    private function request(): PendingRequest
    {
        return $this->configure(Http::createPendingRequest());
    }

    private function configure(PendingRequest $request): PendingRequest
    {
        return $request->baseUrl(self::BASE_URL)
            ->withUserAgent($this->userAgent)
            ->acceptJson()
            ->withToken($this->botToken, 'Bot')
            ->withMiddleware($this->rateLimiter)
            ->retry(
                times: 2,
                sleepMilliseconds: fn (int $attempt, mixed $exception): int => $this->rateLimiter->retryDelayInMilliseconds($exception),
                when: fn (mixed $exception): bool => $this->rateLimiter->shouldRetry($exception),
                throw: false,
            );
    }

    /**
     * @template TResult
     *
     * @param  DiscordReadRequest<TResult>  $request
     * @return TResult
     */
    private function read(DiscordReadRequest $request): mixed
    {
        return $request->parse($this->request()->throw()->get($request->path));
    }

    private function withMessage(DiscordMessage $message): PendingRequest
    {
        $request = $this->request();

        if (! $message->isMultipart()) {
            return $request->asJson();
        }

        $request->attach('payload_json', $message->toJson());

        foreach ($message->getFiles() as $index => $file) {
            $request->attach("files[{$index}]", $file->content, $file->filename);
        }

        return $request;
    }

    /**
     * @return array<string, mixed>
     */
    private function messageData(DiscordMessage $message): array
    {
        return $message->isMultipart() ? [] : $message->toArray();
    }

    /**
     * @throws InvalidConfigurationException
     */
    private function applicationId(): string
    {
        return $this->optionalApplicationId() ?? throw new InvalidConfigurationException('discord-interactions.application_id');
    }

    private function optionalApplicationId(): ?string
    {
        $applicationId = config('discord-interactions.application_id');

        return is_string($applicationId) ? $applicationId : null;
    }

    /**
     * @return positive-int
     */
    private function poolConcurrency(): int
    {
        $concurrency = config('discord-interactions.pool_concurrency', 5);

        return is_numeric($concurrency) ? max(1, (int) $concurrency) : 5;
    }
}
