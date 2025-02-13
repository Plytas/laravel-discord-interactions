<?php

namespace Plytas\Discord;

use Closure;
use Exception;
use Plytas\Discord\Contracts\DiscordComponentHandler;
use Plytas\Discord\Data\DiscordInteraction;
use Plytas\Discord\Data\DiscordResponse;
use Plytas\Discord\Enums\InteractionType;
use Plytas\Discord\Events\DiscordMessageComponentInteractionEvent;
use Plytas\Discord\Events\DiscordModalInteractionEvent;

class DiscordComponentRegistry
{
    /** @var array<string, class-string<DiscordComponentHandler>> */
    private static array $componentHandlers = [];

    /** @var (Closure(DiscordInteraction): DiscordResponse)|null */
    private static ?Closure $handleComponentsUsing = null;

    /**
     * @param  array<string, class-string<DiscordComponentHandler>>  $componentHandlers
     */
    public static function setComponentHandlers(array $componentHandlers): void
    {
        self::$componentHandlers = $componentHandlers;
    }

    /**
     * @param  Closure(DiscordInteraction): DiscordResponse  $callback
     */
    public static function handleComponentsUsing(Closure $callback): void
    {
        self::$handleComponentsUsing = $callback;
    }

    public static function new(): self
    {
        return new self;
    }

    /**
     * @throws Exception
     */
    public function handle(DiscordInteraction $interaction): DiscordResponse
    {
        match ($interaction->type) {
            InteractionType::MESSAGE_COMPONENT => event(new DiscordMessageComponentInteractionEvent($interaction)),
            InteractionType::MODAL_SUBMIT => event(new DiscordModalInteractionEvent($interaction)),
            default => null,
        };

        if (self::$handleComponentsUsing !== null) {
            return (self::$handleComponentsUsing)($interaction);
        }

        $componentId = match ($interaction->type) {
            InteractionType::MESSAGE_COMPONENT => $interaction->getMessageComponent()->custom_id,
            InteractionType::MODAL_SUBMIT => $interaction->getModalComponent()->custom_id,
            default => throw new Exception('Invalid interaction type'),
        };

        $componentHandler = self::$componentHandlers[$componentId] ?? null;

        if ($componentHandler === null) {
            return $interaction->pong(); // Maybe acknowledge the interaction
        }

        if (! is_a($componentHandler, DiscordComponentHandler::class, true)) {
            throw new Exception('Invalid component handler');
        }

        return (new $componentHandler)->handle($interaction);
    }
}
