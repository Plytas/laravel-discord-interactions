<?php

namespace Plytas\Discord\Components;

use Plytas\Discord\Contracts\DiscordComponent;
use Plytas\Discord\Enums\ComponentType;
use Plytas\Discord\Enums\TextInputStyle;
use Spatie\LaravelData\Data;

class TextInput extends Data implements DiscordComponent
{
    public ComponentType $type = ComponentType::TextInput;

    public function __construct(

        public string $custom_id,
        public string $label,
        public ?string $placeholder = null,
        public ?string $value = null,
        public ?bool $required = true,
        public ?int $min_length = null,
        public ?int $max_length = null,
        public TextInputStyle $style = TextInputStyle::Short,
    ) {}

    public static function new(string $custom_id, string $label): self
    {
        return new self(custom_id: $custom_id, label: $label);
    }

    public function setCustomId(string $customId): self
    {
        $this->custom_id = $customId;

        return $this;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function setPlaceholder(?string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function setRequired(?bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    public function setMinLength(?int $minLength): self
    {
        $this->min_length = $minLength;

        return $this;
    }

    public function setMaxLength(?int $maxLength): self
    {
        $this->max_length = $maxLength;

        return $this;
    }

    public function setStyle(TextInputStyle $style): self
    {
        $this->style = $style;

        return $this;
    }

    public function paragraph(): self
    {
        $this->style = TextInputStyle::Paragraph;

        return $this;
    }

    public function short(): self
    {
        $this->style = TextInputStyle::Short;

        return $this;
    }
}
