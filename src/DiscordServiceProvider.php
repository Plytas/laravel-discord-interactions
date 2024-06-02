<?php

namespace Plytas\Discord;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Plytas\Discord\Commands\DiscordCommand;

class DiscordServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-discord-interactions')
            ->hasConfigFile()
            ->hasCommand(DiscordCommand::class);
    }
}
