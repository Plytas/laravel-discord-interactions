<?php

namespace Plytas\Discord\Data;

use Spatie\LaravelData\Data;

class DiscordRole extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description,
        public int $color,
    ) {
    }

    public function colorToHex(): string
    {
        return '#'.dechex($this->color);
    }
}
