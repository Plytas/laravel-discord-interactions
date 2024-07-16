<?php

namespace Plytas\Discord\Data;

use Illuminate\Support\Collection;
use Plytas\Discord\Enums\ComponentType;
use Spatie\LaravelData\Data;

class DiscordModalActionRow extends Data
{
    public function __construct(
        public ComponentType $type,
        /** @var Collection<int, DiscordModalTextInput> */
        public Collection $components,
    ) {}
}
