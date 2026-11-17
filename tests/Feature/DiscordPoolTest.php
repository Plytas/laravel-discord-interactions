<?php

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Plytas\Discord\Data\DiscordChannel;
use Plytas\Discord\Data\DiscordRole;
use Plytas\Discord\DiscordPool;
use Plytas\Discord\Facades\Discord;

beforeEach(function () {
    config([
        'app.url' => 'https://example.com',
        'discord-interactions.bot_token' => 'token',
    ]);
});

it('returns the typed result of each pooled request with the keys of the callback', function () {
    Http::fake([
        'discord.com/api/v10/guilds/1/channels' => Http::response([
            ['id' => '10', 'name' => 'raids', 'type' => 0, 'guild_id' => '1', 'parent_id' => null, 'position' => 0],
        ]),
        'discord.com/api/v10/guilds/1/roles' => Http::response([
            ['id' => '20', 'name' => 'Raiders', 'description' => null, 'color' => 0],
        ]),
    ]);

    $results = Discord::pool(fn (DiscordPool $pool) => [
        'roles' => $pool->getRoles('1'),
        'channels' => $pool->getChannels('1'),
    ]);

    expect(array_keys($results))->toBe(['roles', 'channels'])
        ->and($results['roles']->sole())->toBeInstanceOf(DiscordRole::class)
        ->and($results['channels']->sole())->toBeInstanceOf(DiscordChannel::class)
        ->and($results['channels']->sole()->name)->toBe('raids');
    Http::assertSentCount(2);
});

it('throws when a pooled request fails', function () {
    Http::fake([
        'discord.com/api/v10/guilds/1/channels' => Http::response([]),
        'discord.com/api/v10/guilds/1/roles' => Http::response(['message' => 'Missing Access', 'code' => 50001], 403),
    ]);

    expect(fn () => Discord::pool(fn (DiscordPool $pool) => [$pool->getChannels('1'), $pool->getRoles('1')]))
        ->toThrow(RequestException::class);
});

it('does not keep request options between calls', function () {
    Http::fake([
        'discord.com/api/v10/guilds/1/channels' => Http::response([]),
        'discord.com/api/v10/channels/9/messages/10' => Http::response(['message' => 'Unknown Message'], 404),
    ]);

    Discord::getChannels('1');

    expect(Discord::deleteMessage('9', '10')->status())->toBe(404);
});
