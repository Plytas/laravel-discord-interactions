<?php

namespace Plytas\Discord\Commands;

use Illuminate\Console\Command;
use Plytas\Discord\DiscordCommandRegistry;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'discord:register-commands',
    description: 'Registers discord bot commands.'
)]
class RegisterCommandsCommand extends Command
{
    public $signature = 'discord:register-commands';

    public $description = 'Registers discord bot commands.';

    public function handle(): int
    {
        DiscordCommandRegistry::new()->register();

        return self::SUCCESS;
    }
}
