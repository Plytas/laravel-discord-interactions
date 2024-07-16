<?php

namespace Plytas\Discord\Data;

use Illuminate\Support\Collection;
use Plytas\Discord\Enums\CommandType;
use Spatie\LaravelData\Data;

class DiscordApplicationCommand extends Data
{
    public function __construct(
        public string $name,
        public CommandType $type,
        public ?string $description = null,
        /** @var Collection<int, DiscordApplicationCommandOption> */
        public ?Collection $options = null,
    ) {
    }

    public static function new(string $name, CommandType $type): self
    {
        return new self($name, $type);
    }

    public function setDescription(string $description): self
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

    public function getCommandStructure(): string
    {
        return $this->name.$this->getOptionName($this->options?->first());
    }

    private function getOptionName(?DiscordApplicationCommandOption $option): string
    {
        if (! $option instanceof DiscordApplicationCommandOption) {
            return '';
        }

        return '.'.$option->name.$this->getOptionName($option->options?->first());
    }
}
