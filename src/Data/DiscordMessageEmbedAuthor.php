<?php

namespace Plytas\Discord\Data;

use Spatie\LaravelData\Data;

class DiscordMessageEmbedAuthor extends Data
{
    public function __construct(
        public string $name,
        public ?string $url = null,
        public ?string $icon_url = null,
        public ?string $proxy_icon_url = null,
    ) {}
}
