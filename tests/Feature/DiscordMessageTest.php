<?php

use Plytas\Discord\Data\DiscordMessage;

it('serializes a reply as a message reference', function () {
    $array = DiscordMessage::new()
        ->setContent('Reminder')
        ->replyTo('123456789')
        ->toArray();

    expect($array['message_reference'])->toBe([
        'message_id' => '123456789',
        'fail_if_not_exists' => true,
    ]);
});

it('omits the message reference when the message is not a reply', function () {
    $array = DiscordMessage::new()->setContent('Hello')->toArray();

    expect($array)->not->toHaveKey('message_reference');
});
