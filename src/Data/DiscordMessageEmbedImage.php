<?php

namespace Plytas\Discord\Data;

use Spatie\LaravelData\Data;

class DiscordMessageEmbedImage extends Data
{
    public function __construct(
        public string $url,
        public ?string $proxy_url = null,
        public ?int $height = null,
        public ?int $width = null,
    ) {
    }
}
