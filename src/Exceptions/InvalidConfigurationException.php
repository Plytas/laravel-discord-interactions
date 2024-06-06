<?php

namespace Plytas\Discord\Exceptions;

use Exception;

class InvalidConfigurationException extends Exception
{
    public function __construct(string $configKey)
    {
        parent::__construct("Invalid configuration for key `{$configKey}`");
    }
}
