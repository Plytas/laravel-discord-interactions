<?php

namespace Plytas\Discord;

use Plytas\Discord\Commands\RegisterCommandsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DiscordServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-discord-interactions')
            ->hasConfigFile()
            ->hasRoute('discord')
            ->hasCommand(RegisterCommandsCommand::class);
    }
}
