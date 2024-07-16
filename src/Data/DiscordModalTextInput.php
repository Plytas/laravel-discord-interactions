<?php

namespace Plytas\Discord\Data;

use Plytas\Discord\Enums\ComponentType;
use Spatie\LaravelData\Data;

class DiscordModalTextInput extends Data
{
    public function __construct(
        public string $custom_id,
        public string $value,
        public ComponentType $type,
    ) {
    }
}
