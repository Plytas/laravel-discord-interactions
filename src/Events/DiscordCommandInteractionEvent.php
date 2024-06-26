<?php

namespace Plytas\Discord\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Plytas\Discord\Data\DiscordInteraction;

class DiscordCommandInteractionEvent
{
    use Dispatchable;

    public function __construct(
        public DiscordInteraction $interaction
    ) {}
}
