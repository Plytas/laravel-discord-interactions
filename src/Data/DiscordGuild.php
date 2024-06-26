<?php

namespace Plytas\Discord\Data;

use Spatie\LaravelData\Data;

class DiscordGuild extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $icon = null,
    ) {}
}
