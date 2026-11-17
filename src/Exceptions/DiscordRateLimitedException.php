<?php

namespace Plytas\Discord\Exceptions;

use RuntimeException;

/**
 * The request was not sent, because Discord rate limits it for longer than the configured max_wait.
 */
class DiscordRateLimitedException extends RuntimeException
{
    public function __construct(
        public readonly string $route,
        public readonly float $retryAfter,
    ) {
        parent::__construct(sprintf('Discord rate limits [%s] for %.3F more seconds.', $route, $retryAfter));
    }
}
