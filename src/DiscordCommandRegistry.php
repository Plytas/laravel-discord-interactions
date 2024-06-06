<?php

namespace Plytas\Discord;

use Exception;
use Illuminate\Support\Arr;
use Plytas\Discord\Contracts\DiscordCommand;
use Plytas\Discord\Data\DiscordApplicationCommand;
use Plytas\Discord\Data\DiscordApplicationCommandOption;
use Plytas\Discord\Data\DiscordInteraction;
use Plytas\Discord\Data\DiscordResponse;
use Plytas\Discord\Enums\CommandOptionType;
use Plytas\Discord\Enums\CommandType;
use Plytas\Discord\Events\DiscordCommandInteractionEvent;
use Plytas\Discord\Facades\Discord;

class DiscordCommandRegistry
{
    /** @var array<string, class-string<DiscordCommand>|array<string, class-string<DiscordCommand>|array<string, class-string<DiscordCommand>>>> */
    private static array $commands = [];

    /**
     * @param  array<string, class-string<DiscordCommand>|array<string, class-string<DiscordCommand>|array<string, class-string<DiscordCommand>>>>  $commands
     */
    public static function setCommands(array $commands): void
    {
        self::$commands = $commands;
    }

    public static function new(): self
    {
        return new self();
    }

    public function register(): void
    {
        foreach (self::$commands as $name => $command) {
            $subCommandGroups = [];
            $commandDescription = "$name command";

            if (is_array($command)) {
                foreach ($command as $subGroupName => $subCommandGroup) {
                    $subCommands = [];
                    $subCommandGroupDescription = "$subGroupName subcommand group";

                    if (is_array($subCommandGroup)) {
                        foreach ($subCommandGroup as $subCommandName => $subCommand) {
                            $subCommands[] = DiscordApplicationCommandOption::new(name: $subCommandName, type: CommandOptionType::SUB_COMMAND)
                                ->setDescription((new $subCommand)->description());
                        }
                    } else {
                        $subCommandGroupDescription = (new $subCommandGroup)->description();
                    }

                    $subCommandGroups[] = DiscordApplicationCommandOption::new(name: $subGroupName, type: CommandOptionType::SUB_COMMAND_GROUP)
                        ->setDescription($subCommandGroupDescription)
                        ->setOptions(($subCommands === []) ? null : DiscordApplicationCommandOption::collect(collect($subCommands)));
                }
            } else {
                $commandDescription = (new $command)->description();
            }

            $commandData = DiscordApplicationCommand::new(name: $name, type: CommandType::ChatInput)
                ->setDescription($commandDescription)
                ->setOptions(($subCommandGroups === []) ? null : DiscordApplicationCommandOption::collect(collect($subCommandGroups)));

            Discord::createCommand($commandData);
        }
    }

    /**
     * @throws Exception
     */
    public function handle(DiscordInteraction $interaction): DiscordResponse
    {
        event(new DiscordCommandInteractionEvent($interaction));

        $command = Arr::get(self::$commands, $interaction->getApplicationCommand()->getCommandStructure());

        if (! is_string($command) || ! is_a($command, DiscordCommand::class, true)) {
            throw new Exception('Invalid command');
        }

        return (new $command)->handle($interaction);
    }
}
