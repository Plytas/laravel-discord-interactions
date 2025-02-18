<?php

namespace Plytas\Discord\Data;

use Illuminate\Support\Collection;
use Plytas\Discord\Enums\CommandOptionType;
use Spatie\LaravelData\Data;

class DiscordInteractionApplicationCommandOption extends Data
{
    public function __construct(
        public string $name,
        public CommandOptionType $type,
        public mixed $value = null,
        /** @var Collection<int, DiscordInteractionApplicationCommandOption> */
        public ?Collection $options = null,
    ) {}
}
