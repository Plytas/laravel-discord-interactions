<?php

namespace Plytas\Discord\Data;

use Spatie\LaravelData\Data;

class DiscordUser extends Data
{
    public function __construct(
        public string $id,
        public string $username,
        public string $discriminator,
        public ?string $global_name = null,
        public ?string $avatar = null,
        public ?bool $bot = false,
        public ?bool $system = false,
    ) {
    }
}
