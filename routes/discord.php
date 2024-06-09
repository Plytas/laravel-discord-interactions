<?php

use Illuminate\Support\Facades\Route;
use Plytas\Discord\Exceptions\InvalidConfigurationException;
use Plytas\Discord\Http\Controllers\DiscordController;
use Plytas\Discord\Http\Middleware\ForceJsonResponseMiddleware;
use Plytas\Discord\Http\Middleware\VerifyDiscordSignatureMiddleware;

$beforeMiddleware = config('discord-interactions.route.middleware.before', []);
$afterMiddleware = config('discord-interactions.route.middleware.after', []);

if (! is_array($beforeMiddleware) || ! is_array($afterMiddleware)) {
    throw new InvalidConfigurationException('discord-interactions.middleware');
}

$beforeMiddleware = array_filter($beforeMiddleware, fn (mixed $middleware) => is_string($middleware));
$afterMiddleware = array_filter($afterMiddleware, fn (mixed $middleware) => is_string($middleware));

$path = config('discord-interactions.route.path');

if (! is_string($path)) {
    throw new InvalidConfigurationException('discord-interactions.route.path');
}

Route::post($path, DiscordController::class)
    ->middleware([
        ...$beforeMiddleware,
        ForceJsonResponseMiddleware::class,
        VerifyDiscordSignatureMiddleware::class,
        ...$afterMiddleware,
    ]);
