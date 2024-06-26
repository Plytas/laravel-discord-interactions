<?php

namespace Plytas\Discord\Data;

use Illuminate\Support\Collection;
use Plytas\Discord\Enums\EmbedColor;
use Spatie\LaravelData\Data;

class DiscordMessageEmbed extends Data
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public int|EmbedColor $color = EmbedColor::Default,
        /** @var Collection<int, DiscordMessageEmbedField> */
        public Collection $fields = new Collection(),
    ) {}

    public static function new(): self
    {
        return new self();
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setColor(int|EmbedColor $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function addField(?DiscordMessageEmbedField $field): self
    {
        if (! $field instanceof DiscordMessageEmbedField) {
            return $this;
        }

        $this->fields->push($field);

        return $this;
    }
}
