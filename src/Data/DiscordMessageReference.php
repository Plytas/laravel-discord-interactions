<?php

namespace Plytas\Discord\Data;

use Spatie\LaravelData\Data;

/**
 * Makes a message a reply. Discord shows the referenced message quoted above the reply.
 */
class DiscordMessageReference extends Data
{
    public function __construct(
        public string $message_id,
        public bool $fail_if_not_exists = true,
    ) {}
}
