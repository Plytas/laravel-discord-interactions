<?php

namespace Plytas\Discord\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class DiscordModalComponent extends Data
{
    public function __construct(
        public string $custom_id,
        /** @var Collection<int, DiscordModalActionRow> */
        public Collection $components,
    ) {}

    /**
     * @return Collection<string, DiscordModalTextInput>
     */
    public function getKeyedComponents(): Collection
    {
        return $this->components
            ->mapWithKeys(static function (DiscordModalActionRow $actionRow): array {
                $component = $actionRow->components->first();

                if ($component === null) {
                    return [];
                }

                return [$component->custom_id => $component];
            })
            ->filter();
    }
}
