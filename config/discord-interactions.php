<?php

return [
    'application_id' => env('DISCORD_APPLICATION_ID'),
    'public_key' => env('DISCORD_PUBLIC_KEY'),
    'bot_token' => env('DISCORD_BOT_TOKEN'),

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
