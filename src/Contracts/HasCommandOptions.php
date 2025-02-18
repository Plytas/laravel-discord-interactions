<?php

namespace Plytas\Discord\Contracts;

use Illuminate\Support\Collection;
use Plytas\Discord\Data\DiscordApplicationCommandOption;

interface HasCommandOptions
{
    /**
     * @return Collection<int, DiscordApplicationCommandOption>
     */
    public function commandOptions(): Collection;
}
