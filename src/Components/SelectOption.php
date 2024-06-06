<?php

namespace Plytas\Discord\Components;

use Plytas\Discord\Data\DiscordEmoji;
use Spatie\LaravelData\Data;

class SelectOption extends Data
{
    public function __construct(
        public string $label,
        public string $value,
        public ?string $description = null,
        public ?DiscordEmoji $emoji = null,
        public ?bool $default = null,
    ) {
    }

    public static function new(string $label, string $value): self
    {
        return new self(label: $label, value: $value);
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setEmoji(DiscordEmoji|string $emoji): self
    {
        if (is_string($emoji)) {
            $emoji = DiscordEmoji::new($emoji);
        }

        $this->emoji = $emoji;

        return $this;
    }

    public function setDefault(?bool $default): self
    {
        $this->default = $default;

        return $this;
    }
}
