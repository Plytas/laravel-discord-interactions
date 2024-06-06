<?php

namespace Plytas\Discord\Facades;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Facade;
use Plytas\Discord\Data\DiscordApplicationCommand;
use Plytas\Discord\Data\DiscordInteraction;
use Plytas\Discord\Data\DiscordMessage;

/**
 * @method static Response createCommand(DiscordApplicationCommand $command)
 * @method static Response updateInteractionMessage(DiscordInteraction $interaction, DiscordMessage $message)
 * @method static Response deleteInteractionMessage(DiscordInteraction $interaction)
 * @method static Response createMessage(string $channelId, DiscordMessage $message)
 * @method static Response updateMessage(string $channelId, string $messageId, DiscordMessage $message)
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
