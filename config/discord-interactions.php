<?php

return [
    'application_id' => env('DISCORD_APPLICATION_ID'),
    'public_key' => env('DISCORD_PUBLIC_KEY'),
    'bot_token' => env('DISCORD_BOT_TOKEN'),

    'rate_limit' => [
        // The cache store that keeps the Discord rate limit buckets. Use a store that all workers share, for example
        // redis or database. Null uses the default cache store.
        'cache_store' => env('DISCORD_RATE_LIMIT_CACHE_STORE'),

        // The longest time, in seconds, that a request waits for a rate limit. A request that must wait longer throws
        // DiscordRateLimitedException, and a 429 response with a longer retry_after is not retried.
        'max_wait' => (float) env('DISCORD_RATE_LIMIT_MAX_WAIT', 2),
    ],

    // The number of requests that Discord::pool() sends at the same time.
    'pool_concurrency' => (int) env('DISCORD_POOL_CONCURRENCY', 5),

    'route' => [
        'path' => env('DISCORD_ROUTE', '/discord'),

        'middleware' => [
            'before' => [
                // ThrottleRequests::class,
            ],
            'after' => [

            ],
        ],
    ],
];
