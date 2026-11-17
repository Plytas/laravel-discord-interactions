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
 * Collects the read requests for Discord::pool(). Each method returns a request that Discord::pool() replaces with
 * the same result as the method of the same name on Discord.
 */
final class DiscordPool
{
    /** @var list<DiscordReadRequest<mixed>> */
    private array $requests = [];

    public function __construct(
        private readonly ?string $applicationId,
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

        return $this->add(DiscordReadRequest::commands($this->applicationId));
    }

    /**
     * @return DiscordReadRequest<DiscordUser>
     */
    public function getUser(string $userId): DiscordReadRequest
    {
        return $this->add(DiscordReadRequest::user($userId));
    }

    /**
     * @return DiscordReadRequest<DiscordGuild>
     */
    public function getGuild(string $guildId): DiscordReadRequest
    {
        return $this->add(DiscordReadRequest::guild($guildId));
    }

    /**
     * @return DiscordReadRequest<Collection<int, DiscordChannel>>
     */
    public function getChannels(string $guildId): DiscordReadRequest
    {
        return $this->add(DiscordReadRequest::channels($guildId));
    }

    /**
     * @return DiscordReadRequest<Collection<int, DiscordRole>>
     */
    public function getRoles(string $guildId): DiscordReadRequest
    {
        return $this->add(DiscordReadRequest::roles($guildId));
    }

    /**
     * @return list<DiscordReadRequest<mixed>>
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    /**
     * @template TResult
     *
     * @param  DiscordReadRequest<TResult>  $request
     * @return DiscordReadRequest<TResult>
     */
    private function add(DiscordReadRequest $request): DiscordReadRequest
    {
        $this->requests[] = $request;

        return $request;
    }
}
