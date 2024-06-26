<?php

namespace Plytas\Discord;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Plytas\Discord\Data\DiscordApplicationCommand;
use Plytas\Discord\Data\DiscordChannel;
use Plytas\Discord\Data\DiscordGuild;
use Plytas\Discord\Data\DiscordInteraction;
use Plytas\Discord\Data\DiscordMessage;
use Plytas\Discord\Data\DiscordRole;
use Plytas\Discord\Exceptions\InvalidConfigurationException;

readonly class Discord
{
    private PendingRequest $client;

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

        $this->client = Http::baseUrl('https://discord.com/api/v10')
            ->withUserAgent("DiscordBot ($appUrl, 1)")
            ->acceptJson()
            ->asJson()
            ->withToken($botToken, 'Bot');
    }

    public function createCommand(DiscordApplicationCommand $command): Response
    {
        $applicationId = config('discord-interactions.application_id');

        if (! is_string($applicationId)) {
            throw new InvalidConfigurationException('discord-interactions.application_id');
        }

        return $this->client->post("/applications/{$applicationId}/commands", $command->toArray());
    }

    public function updateInteractionMessage(DiscordInteraction $interaction, DiscordMessage $message): Response
    {
        return $this->client->patch("/webhooks/{$interaction->application_id}/{$interaction->token}/messages/@original", $message->toArray());
    }

    public function deleteInteractionMessage(DiscordInteraction $interaction): Response
    {
        return $this->client->delete("/webhooks/{$interaction->application_id}/{$interaction->token}/messages/@original");
    }

    public function createMessage(string $channelId, DiscordMessage $message): Response
    {
        return $this->client->post("/channels/{$channelId}/messages", $message->toArray());
    }

    public function updateMessage(string $channelId, string $messageId, DiscordMessage $message): Response
    {
        return $this->client->patch("/channels/{$channelId}/messages/{$messageId}", $message->toArray());
    }

    public function deleteMessage(string $channelId, string $messageId): Response
    {
        return $this->client->delete("/channels/{$channelId}/messages/{$messageId}");
    }

    public function getGuild(string $guildId): DiscordGuild
    {
        return DiscordGuild::from($this->client->get("/guilds/{$guildId}")->json());
    }

    /**
     * @return Collection<int, DiscordChannel>
     */
    public function getChannels(string $guildId): Collection
    {
        return DiscordChannel::collect($this->client->throw()->get("/guilds/{$guildId}/channels")->collect());
    }

    /**
     * @return Collection<int, DiscordRole>
     */
    public function getRoles(string $guildId): Collection
    {
        return DiscordRole::collect($this->client->throw()->get("/guilds/{$guildId}/roles")->collect());
    }
}
