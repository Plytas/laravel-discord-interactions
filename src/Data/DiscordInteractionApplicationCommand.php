<?php

namespace Plytas\Discord\Data;

use Illuminate\Support\Collection;
use Plytas\Discord\Enums\CommandType;
use Spatie\LaravelData\Data;

class DiscordInteractionApplicationCommand extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public CommandType $type,
        /** @var Collection<int, DiscordInteractionApplicationCommandOption> */
        public ?Collection $options = null,
    ) {}

    /**
     * @return Collection<string, mixed>
     */
    public function getKeyedOptionValues(): Collection
    {
        $values = [];

        foreach ($this->options ?? [] as $commandOption) {
            if ($commandOption->type->isSubcommandOrGroup()) {
                foreach ($commandOption->options ?? [] as $subCommandGroupOption) {
                    if ($subCommandGroupOption->type->isSubcommandOrGroup()) {
                        foreach ($subCommandGroupOption->options ?? [] as $subCommandOption) {
                            $values[$subCommandOption->name] = $subCommandOption->value;
                        }

                        continue;
                    }

                    $values[$subCommandGroupOption->name] = $subCommandGroupOption->value;
                }

                continue;
            }

            $values[$commandOption->name] = $commandOption->value;
        }

        return collect($values);
    }

    public function getCommandStructure(): string
    {
        return $this->name.$this->getOptionName($this->options?->first());
    }

    private function getOptionName(?DiscordInteractionApplicationCommandOption $option): string
    {
        if (! $option instanceof DiscordInteractionApplicationCommandOption) {
            return '';
        }

        if (! $option->type->isSubcommandOrGroup()) {
            return '';
        }

        return '.'.$option->name.$this->getOptionName($option->options?->first());
    }
}
