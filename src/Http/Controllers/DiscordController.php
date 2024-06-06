<?php

namespace Plytas\Discord\Http\Controllers;

use Exception;
use Plytas\Discord\Data\DiscordInteraction;
use Plytas\Discord\Data\DiscordResponse;
use Plytas\Discord\DiscordCommandRegistry;
use Plytas\Discord\DiscordComponentRegistry;
use Plytas\Discord\Enums\InteractionType;

class DiscordController
{
    /**
     * @throws Exception
     */
    public function __invoke(DiscordInteraction $interaction): DiscordResponse
    {
        return match ($interaction->type) {
            InteractionType::PING => $interaction->pong(),
            InteractionType::APPLICATION_COMMAND => DiscordCommandRegistry::new()->handle($interaction),
            InteractionType::MESSAGE_COMPONENT,
            InteractionType::MODAL_SUBMIT => DiscordComponentRegistry::new()->handle($interaction),
            default => throw new Exception('Unknown interaction type'),
        };
    }
}
