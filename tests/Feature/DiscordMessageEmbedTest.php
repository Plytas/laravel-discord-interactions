<?php

use Plytas\Discord\Data\DiscordMessageEmbed;
use Plytas\Discord\Data\DiscordMessageEmbedAuthor;
use Plytas\Discord\Data\DiscordMessageEmbedImage;
use Plytas\Discord\Data\DiscordMessageEmbedThumbnail;

it('serializes author, thumbnail and image', function () {
    $embed = DiscordMessageEmbed::new()
        ->setTitle('Store')
        ->setAuthor(new DiscordMessageEmbedAuthor(name: 'W', icon_url: 'https://x/l.png'))
        ->setThumbnail(new DiscordMessageEmbedThumbnail(url: 'https://x/l.png'))
        ->setImage(new DiscordMessageEmbedImage(url: 'https://x/s.jpg'));

    $array = $embed->toArray();

    expect($array['title'])->toBe('Store')
        ->and($array['author']['name'])->toBe('W')
        ->and($array['author']['icon_url'])->toBe('https://x/l.png')
        ->and($array['thumbnail']['url'])->toBe('https://x/l.png')
        ->and($array['image']['url'])->toBe('https://x/s.jpg');
});

it('serializes a bare embed with null author and thumbnail', function () {
    $embed = DiscordMessageEmbed::new()->setTitle('Store');

    $array = $embed->toArray();

    expect($array['title'])->toBe('Store')
        ->and($array['author'])->toBeNull()
        ->and($array['thumbnail'])->toBeNull()
        ->and($array['image'])->toBeNull();
});
