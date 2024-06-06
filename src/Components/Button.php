<?php

namespace Plytas\Discord\Components;

use Plytas\Discord\Contracts\DiscordComponent;
use Plytas\Discord\Data\DiscordEmoji;
use Plytas\Discord\Enums\ButtonStyle;
use Plytas\Discord\Enums\ComponentType;
use Spatie\LaravelData\Data;

class Button extends Data implements DiscordComponent
{
    public ComponentType $type = ComponentType::Button;

    public function __construct(
        public string $custom_id = '',
        public string $label = '',
        public ?DiscordEmoji $emoji = null,
        public ?bool $disabled = null,
        public ?string $url = null,
        public ButtonStyle $style = ButtonStyle::Primary,
    ) {
    }

    public static function new(): self
    {
        return new self();
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

    public function setDisabled(?bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function setStyle(ButtonStyle $style): self
    {
        $this->style = $style;

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
}
