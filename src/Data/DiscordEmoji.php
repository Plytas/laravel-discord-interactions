<?php

namespace Plytas\Discord\Data;

use Spatie\LaravelData\Data;

class DiscordEmoji extends Data
{
    public function __construct(
        public string $name,
    ) {
    }

    public static function new(string $name): self
    {
        return new self($name);
    }
}
