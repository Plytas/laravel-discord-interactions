<?php

namespace Plytas\Discord\Data;

use Illuminate\Support\Collection;
use Plytas\Discord\Enums\CommandOptionType;
use Spatie\LaravelData\Data;

class DiscordApplicationCommandOption extends Data
{
    public function __construct(
        public string $name,
        public CommandOptionType $type,
        public ?string $description = null,
        /** @var Collection<int, DiscordApplicationCommandOption> */
        public ?Collection $options = null,
        public bool $required = false,
    ) {}

    public static function new(string $name, CommandOptionType $type): self
    {
        return new self($name, $type);
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param  Collection<int, DiscordApplicationCommandOption>|null  $options
     */
    public function setOptions(?Collection $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function setRequired(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }
}
