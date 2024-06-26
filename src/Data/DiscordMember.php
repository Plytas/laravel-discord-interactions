<?php

namespace Plytas\Discord\Data;

use Spatie\LaravelData\Data;

class DiscordMember extends Data
{
    public function __construct(
        public ?DiscordUser $user = null,
    ) {}
}
