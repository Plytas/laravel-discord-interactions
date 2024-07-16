<?php

namespace Plytas\Discord\Data;

use Plytas\Discord\Enums\ComponentType;
use Spatie\LaravelData\Data;

class DiscordMessageComponent extends Data
{
    public function __construct(

        public ComponentType $component_type,
        public string $custom_id,
        /**
         * @var array<int, string>
         */
        public ?array $values,
    ) {
    }
}
