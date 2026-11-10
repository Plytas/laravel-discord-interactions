<?php

use Illuminate\Support\Facades\Http;
use Plytas\Discord\Enums\ChannelType;
use Plytas\Discord\Facades\Discord;

beforeEach(function () {
    config([
        'app.url' => 'https://example.com',
        'discord-interactions.bot_token' => 'token',
    ]);
});

it('skips channels with an unknown type', function () {
    Http::fake([
        'discord.com/api/v10/guilds/1/channels' => Http::response([
            ['id' => '10', 'name' => 'general', 'type' => 0, 'guild_id' => '1', 'parent_id' => null, 'position' => 0],
            ['id' => '11', 'name' => 'server-hub', 'type' => 20, 'guild_id' => '1', 'parent_id' => null, 'position' => 1],
            ['id' => '12', 'name' => 'Events', 'type' => 4, 'guild_id' => '1', 'parent_id' => null, 'position' => 2],
        ]),
    ]);

    $channels = Discord::getChannels('1');

    expect($channels->pluck('id')->all())->toBe(['10', '12'])
        ->and($channels->pluck('type')->all())->toBe([ChannelType::GuildText, ChannelType::GuildCategory]);
});
