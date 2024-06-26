<?php

namespace Plytas\Discord\Data;

use Plytas\Discord\Enums\ChannelType;
use Spatie\LaravelData\Data;

class DiscordChannel extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public ChannelType $type,
        public string $guild_id,
        public ?string $parent_id,
        public int $position,
    ) {}
}
