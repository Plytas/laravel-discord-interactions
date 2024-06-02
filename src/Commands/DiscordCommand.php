<?php

namespace Plytas\Discord\Commands;

use Illuminate\Console\Command;

class DiscordCommand extends Command
{
    public $signature = 'laravel-discord-interactions';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
