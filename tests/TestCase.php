<?php

namespace Plytas\Discord\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Plytas\Discord\DiscordServiceProvider;
use Spatie\LaravelData\LaravelDataServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelDataServiceProvider::class,
            DiscordServiceProvider::class,
        ];
    }
}
