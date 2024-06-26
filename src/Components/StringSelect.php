<?php

namespace Plytas\Discord\Components;

use Illuminate\Support\Collection;
use Plytas\Discord\Contracts\DiscordComponent;
use Plytas\Discord\Enums\ComponentType;
use Spatie\LaravelData\Data;

class StringSelect extends Data implements DiscordComponent
{
    public ComponentType $type = ComponentType::StringSelect;

    public function __construct(
        public string $custom_id,
        /** @var Collection<int, SelectOption> */
        public Collection $options = new Collection(),
        public ?string $placeholder = null,
        public ?int $min_values = 1,
        public ?int $max_values = 1,
        public ?bool $disabled = false,
    ) {}

    public static function new(string $custom_id): self
    {
        return new self(custom_id: $custom_id);
    }

    public function addOption(SelectOption $option): self
    {
        $this->options->push($option);

        return $this;
    }

    public function setPlaceholder(?string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }
}
