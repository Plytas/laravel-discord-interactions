<?php

namespace Plytas\Discord\Data;

use Plytas\Discord\Enums\ResponseType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class DiscordResponse extends Data
{
    public function __construct(
        public ResponseType $type,
        public Data|Optional $data,
    ) {
    }

    public static function pong(): self
    {
        return new self(ResponseType::PONG, Optional::create());
    }

    public static function deferredMessage(): self
    {
        return new self(ResponseType::DEFERRED_CHANNEL_MESSAGE_WITH_SOURCE, Optional::create());
    }

    public static function message(DiscordMessage $message): self
    {
        return new self(ResponseType::CHANNEL_MESSAGE_WITH_SOURCE, $message);
    }

    public static function updateMessage(DiscordMessage $message): self
    {
        return new self(ResponseType::UPDATE_MESSAGE, $message);
    }

    public static function showModal(DiscordModal $modal): self
    {
        return new self(ResponseType::MODAL, $modal);
    }
}
