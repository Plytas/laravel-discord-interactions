<?php

namespace Plytas\Discord\Components;

use Illuminate\Support\Collection;
use Plytas\Discord\Contracts\DiscordComponent;
use Plytas\Discord\Enums\ComponentType;
use Spatie\LaravelData\Data;

class ActionRow extends Data implements DiscordComponent
{
    public ComponentType $type = ComponentType::ActionRow;

    public function __construct(
        /** @var Collection<int, DiscordComponent> */
        public Collection $components = new Collection,
    ) {}

    public static function new(): self
    {
        return new self;
    }

    public function addComponent(DiscordComponent $component): self
    {
        $this->components->push($component);

        return $this;
    }
}
