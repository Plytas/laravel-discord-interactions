<?php

namespace Plytas\Discord\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Plytas\Discord\Discord
 */
class Discord extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Plytas\Discord\Discord::class;
    }
}
