<?php

namespace Plytas\Discord\Requests;

use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Plytas\Discord\Data\DiscordApplicationCommand;
use Plytas\Discord\Data\DiscordChannel;
use Plytas\Discord\Data\DiscordGuild;
use Plytas\Discord\Data\DiscordRole;
use Plytas\Discord\Data\DiscordUser;
use Plytas\Discord\Enums\ChannelType;

/**
 * A GET request to the Discord API and the parser for its response. Discord and DiscordPool send the same requests.
 *
 * @template TResult
 */
final readonly class DiscordReadRequest
{
    /**
     * @param  Closure(Response): TResult  $parse
     */
    private function __construct(
        public string $path,
        private Closure $parse,
    ) {}

    /**
     * @return self<Collection<int, DiscordApplicationCommand>>
     */
    public static function commands(string $applicationId): self
    {
        return new self(
            "/applications/{$applicationId}/commands",
            static fn (Response $response): Collection => $response->collect()->values()
                ->map(static fn (mixed $command): DiscordApplicationCommand => DiscordApplicationCommand::from($command)),
        );
    }

    /**
     * @return self<DiscordUser>
     */
    public static function user(string $userId): self
    {
        return new self(
            "/users/{$userId}",
            static fn (Response $response): DiscordUser => DiscordUser::from($response->json()),
        );
    }

    /**
     * @return self<DiscordGuild>
     */
    public static function guild(string $guildId): self
    {
        return new self(
            "/guilds/{$guildId}",
            static fn (Response $response): DiscordGuild => DiscordGuild::from($response->json()),
        );
    }

    /**
     * Channels with a type that ChannelType does not know are skipped, because Discord adds new channel types over time.
     *
     * @return self<Collection<int, DiscordChannel>>
     */
    public static function channels(string $guildId): self
    {
        return new self(
            "/guilds/{$guildId}/channels",
            static fn (Response $response): Collection => $response->collect()
                ->whereInStrict('type', array_column(ChannelType::cases(), 'value'))
                ->values()
                ->map(static fn (mixed $channel): DiscordChannel => DiscordChannel::from($channel)),
        );
    }

    /**
     * @return self<Collection<int, DiscordRole>>
     */
    public static function roles(string $guildId): self
    {
        return new self(
            "/guilds/{$guildId}/roles",
            static fn (Response $response): Collection => $response->collect()->values()
                ->map(static fn (mixed $role): DiscordRole => DiscordRole::from($role)),
        );
    }

    /**
     * @return TResult
     */
    public function parse(Response $response): mixed
    {
        return ($this->parse)($response);
    }
}
