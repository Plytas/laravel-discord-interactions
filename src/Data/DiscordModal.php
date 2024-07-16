<?php

namespace Plytas\Discord\Data;

use Illuminate\Support\Collection;
use Plytas\Discord\Contracts\DiscordComponent;
use Spatie\LaravelData\Data;

class DiscordModal extends Data
{
    public function __construct(

        public string $custom_id,
        public string $title,
        /** @var Collection<int, DiscordComponent> */
        public Collection $components = new Collection(),
    ) {
    }

    public static function new(string $custom_id, string $title): self
    {
        return new self($custom_id, $title);
    }

    public function addComponent(DiscordComponent $component): self
    {
        $this->components->push($component);

        return $this;
    }
}
