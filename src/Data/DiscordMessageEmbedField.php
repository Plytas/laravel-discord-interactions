<?php

namespace Plytas\Discord\Data;

use Spatie\LaravelData\Data;

class DiscordMessageEmbedField extends Data
{
    public function __construct(
        public string $name,
        public string $value,
        public bool $inline = false,
    ) {
    }
}
