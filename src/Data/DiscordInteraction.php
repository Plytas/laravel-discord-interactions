<?php

namespace Plytas\Discord\Data;

use Plytas\Discord\Enums\InteractionType;
use Spatie\LaravelData\Data;

class DiscordInteraction extends Data
{
    public function __construct(
        public string $id,
        public string $application_id,
        public InteractionType $type,
        public int $version,
        public string $token,
        public ?DiscordMember $member = null,
        public ?DiscordUser $user = null,
        public ?string $guild_id = null,
        public ?string $channel_id = null,
        /**
         * @var array<string, mixed>
         */
        public ?array $data = [],

        private ?DiscordInteractionApplicationCommand $applicationCommand = null,
        private ?DiscordMessageComponent $messageComponent = null,
        private ?DiscordModalComponent $modalComponent = null,
    ) {}

    public function pong(): DiscordResponse
    {
        return DiscordResponse::pong();
    }

    public function respondWithMessage(DiscordMessage $message): DiscordResponse
    {
        return DiscordResponse::message($message);
    }

    public function updateMessage(DiscordMessage $message): DiscordResponse
    {
        return DiscordResponse::updateMessage($message);
    }

    public function showModal(DiscordModal $modal): DiscordResponse
    {
        return DiscordResponse::showModal($modal);
    }

    public function getApplicationCommand(): DiscordInteractionApplicationCommand
    {
        return $this->applicationCommand ??= DiscordInteractionApplicationCommand::from($this->data);
    }

    public function getMessageComponent(): DiscordMessageComponent
    {
        return $this->messageComponent ??= DiscordMessageComponent::from($this->data);
    }

    public function getModalComponent(): DiscordModalComponent
    {
        return $this->modalComponent ??= DiscordModalComponent::from($this->data);
    }
}
