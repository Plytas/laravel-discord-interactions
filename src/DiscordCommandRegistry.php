<?php

namespace Plytas\Discord;

use Exception;
use Illuminate\Support\Arr;
use Plytas\Discord\Contracts\DiscordCommand;
use Plytas\Discord\Contracts\HasCommandOptions;
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
        return new self;
    }

    public function register(): void
    {
        foreach (self::$commands as $name => $command) {
            $subCommandGroups = [];
            $commandDescription = "$name command";
            $options = null;

            if (is_array($command)) {
                foreach ($command as $subGroupName => $subCommandGroup) {
                    $subCommands = [];
                    $subCommandGroupDescription = "$subGroupName subcommand group";
                    $subCommandOptions = null;

                    if (is_array($subCommandGroup)) {
                        foreach ($subCommandGroup as $subCommandName => $subCommand) {
                            $subCommandCommand = new $subCommand;

                            $subCommands[] = DiscordApplicationCommandOption::new(name: $subCommandName, type: CommandOptionType::SUB_COMMAND)
                                ->setDescription($subCommandCommand->description())
                                ->setOptions($subCommandCommand instanceof HasCommandOptions ? $subCommandCommand->commandOptions() : null);
                        }

                        $subCommandOptions = ($subCommands === []) ? null : DiscordApplicationCommandOption::collect(collect($subCommands));
                    } else {
                        $subCommandGroupCommand = new $subCommandGroup;
                        $subCommandGroupDescription = $subCommandGroupCommand->description();

                        if ($subCommandGroupCommand instanceof HasCommandOptions) {
                            $subCommandOptions = $subCommandGroupCommand->commandOptions();
                        }
                    }

                    $subCommandGroups[] = DiscordApplicationCommandOption::new(name: $subGroupName, type: ($subCommands === []) ? CommandOptionType::SUB_COMMAND : CommandOptionType::SUB_COMMAND_GROUP)
                        ->setDescription($subCommandGroupDescription)
                        ->setOptions($subCommandOptions);
                }

                $options = ($subCommandGroups === []) ? null : DiscordApplicationCommandOption::collect(collect($subCommandGroups));
            } else {
                $applicationCommand = new $command;
                $commandDescription = $applicationCommand->description();

                if ($applicationCommand instanceof HasCommandOptions) {
                    $options = $applicationCommand->commandOptions();
                }
            }

            $commandData = DiscordApplicationCommand::new(name: $name, type: CommandType::ChatInput)
                ->setDescription($commandDescription)
                ->setOptions($options);

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
