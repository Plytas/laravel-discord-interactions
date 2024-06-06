<?php

namespace Plytas\Discord\Contracts;

use Plytas\Discord\Data\DiscordInteraction;
use Plytas\Discord\Data\DiscordResponse;

interface DiscordCommand
{
    public function description(): string;

    public function handle(DiscordInteraction $interaction): DiscordResponse;
}
