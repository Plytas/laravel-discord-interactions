<?php

namespace Plytas\Discord\Contracts;

use Plytas\Discord\Data\DiscordInteraction;
use Plytas\Discord\Data\DiscordResponse;

interface DiscordComponentHandler
{
    public function handle(DiscordInteraction $interaction): DiscordResponse;
}
