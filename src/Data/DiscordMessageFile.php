<?php

namespace Plytas\Discord\Data;

use Spatie\LaravelData\Data;

class DiscordMessageFile extends Data
{
    public function __construct(
        public string $filename,
        public string $content,
    ) {
    }

    public static function new(string $filename, string $content): self
    {
        return new self($filename, $content);
    }
}
