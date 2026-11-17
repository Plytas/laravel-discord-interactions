<?php

namespace Plytas\Discord;

use Illuminate\Support\Collection;
use Plytas\Discord\Data\DiscordApplicationCommand;
use Plytas\Discord\Data\DiscordChannel;
use Plytas\Discord\Data\DiscordGuild;
use Plytas\Discord\Data\DiscordRole;
use Plytas\Discord\Data\DiscordUser;
use Plytas\Discord\Exceptions\InvalidConfigurationException;
use Plytas\Discord\Requests\DiscordReadRequest;

/**
 * Creates the read requests for Discord::pool(). Discord::pool() replaces each request with the same result as the
 * method of the same name on Discord.
 */
final readonly class DiscordPool
{
    public function __construct(
        private ?string $applicationId,
    ) {}

    /**
     * @return DiscordReadRequest<Collection<int, DiscordApplicationCommand>>
     *
     * @throws InvalidConfigurationException
     */
    public function getCommands(): DiscordReadRequest
    {
        if ($this->applicationId === null) {
            throw new InvalidConfigurationException('discord-interactions.application_id');
        }

        return DiscordReadRequest::commands($this->applicationId);
    }

    /**
     * @return DiscordReadRequest<DiscordUser>
     */
    public function getUser(string $userId): DiscordReadRequest
    {
        return DiscordReadRequest::user($userId);
    }

    /**
     * @return DiscordReadRequest<DiscordGuild>
     */
    public function getGuild(string $guildId): DiscordReadRequest
    {
        return DiscordReadRequest::guild($guildId);
    }

    /**
     * @return DiscordReadRequest<Collection<int, DiscordChannel>>
     */
    public function getChannels(string $guildId): DiscordReadRequest
    {
        return DiscordReadRequest::channels($guildId);
    }

    /**
     * @return DiscordReadRequest<Collection<int, DiscordRole>>
     */
    public function getRoles(string $guildId): DiscordReadRequest
    {
        return DiscordReadRequest::roles($guildId);
    }
}
