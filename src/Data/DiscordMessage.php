<?php

namespace Plytas\Discord\Data;

use Illuminate\Support\Collection;
use Plytas\Discord\Contracts\DiscordComponent;
use Spatie\LaravelData\Data;

class DiscordMessage extends Data
{
    public function __construct(
        public ?string $content = null,
        public int $flags = 0,
        /** @var Collection<int, DiscordMessageEmbed> */
        public Collection $embeds = new Collection(),
        /** @var Collection<int, DiscordComponent> */
        public Collection $components = new Collection(),
    ) {
    }

    public static function new(): self
    {
        return new self();
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function ephemeral(): self
    {
        $this->flags |= 1 << 6;

        return $this;
    }

    public function addEmbed(?DiscordMessageEmbed $embed): self
    {
        if (! $embed instanceof DiscordMessageEmbed) {
            return $this;
        }

        $this->embeds->push($embed);

        return $this;
    }

    public function addComponent(DiscordComponent $component): self
    {
        $this->components->push($component);

        return $this;
    }
}
