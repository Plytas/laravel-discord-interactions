<?php

namespace Plytas\Discord\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Plytas\Discord\DiscordServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            DiscordServiceProvider::class,
        ];
    }
}
