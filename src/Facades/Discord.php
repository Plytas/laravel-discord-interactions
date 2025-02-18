<?php

namespace Plytas\Discord\Facades;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Plytas\Discord\Data\DiscordApplicationCommand;
use Plytas\Discord\Data\DiscordChannel;
use Plytas\Discord\Data\DiscordGuild;
use Plytas\Discord\Data\DiscordInteraction;
use Plytas\Discord\Data\DiscordMessage;
use Plytas\Discord\Data\DiscordRole;
use Plytas\Discord\Data\DiscordUser;

/**
 * @method static Response createCommand(DiscordApplicationCommand $command)
 * @method static Collection<int, DiscordApplicationCommand> getCommands()
 * @method static Response deleteCommand(string $commandId)
 * @method static Response updateInteractionMessage(DiscordInteraction $interaction, DiscordMessage $message)
 * @method static Response deleteInteractionMessage(DiscordInteraction $interaction)
 * @method static Response createMessage(string $channelId, DiscordMessage $message)
 * @method static Response updateMessage(string $channelId, string $messageId, DiscordMessage $message)
 * @method static Response deleteMessage(string $channelId, string $messageId)
 * @method static Response openDirectMessageChannel(string $userId)
 * @method static DiscordUser getUser(string $userId)
 * @method static DiscordGuild getGuild(string $guildId)
 * @method static Collection<int, DiscordChannel> getChannels(string $guildId)
 * @method static Collection<int, DiscordRole> getRoles(string $guildId)
 *
 * @see \Plytas\Discord\Discord
 */
class Discord extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Plytas\Discord\Discord::class;
    }
}
