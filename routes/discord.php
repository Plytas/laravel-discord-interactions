<?php

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Plytas\Discord\Exceptions\InvalidConfigurationException;
use Plytas\Discord\Http\Controllers\DiscordController;
use Plytas\Discord\Http\Middleware\ForceJsonResponseMiddleware;
use Plytas\Discord\Http\Middleware\VerifyDiscordSignatureMiddleware;

$beforeMiddleware = config('discord-interactions.middleware.before', []);
$afterMiddleware = config('discord-interactions.middleware.after', []);

if (! is_array($beforeMiddleware) || ! is_array($afterMiddleware)) {
    throw new InvalidConfigurationException('discord-interactions.middleware');
}

$beforeMiddleware = array_filter($beforeMiddleware, fn (mixed $middleware) => is_string($middleware));
$afterMiddleware = array_filter($afterMiddleware, fn (mixed $middleware) => is_string($middleware));

Route::post('/discord', DiscordController::class)
    ->middleware([
        ...$beforeMiddleware,
        ForceJsonResponseMiddleware::class,
        SubstituteBindings::class,
        VerifyDiscordSignatureMiddleware::class,
        ...$afterMiddleware,
    ]);
