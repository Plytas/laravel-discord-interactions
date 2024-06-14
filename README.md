# Laravel (PHP) client that uses Discord HTTP API to create and respond to interactions

[![Latest Version on Packagist](https://img.shields.io/packagist/v/plytas/laravel-discord-interactions.svg?style=flat-square)](https://packagist.org/packages/plytas/laravel-discord-interactions)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/plytas/laravel-discord-interactions/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/plytas/laravel-discord-interactions/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/plytas/laravel-discord-interactions/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/plytas/laravel-discord-interactions/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/plytas/laravel-discord-interactions.svg?style=flat-square)](https://packagist.org/packages/plytas/laravel-discord-interactions)

Create rich Discord interactions and respond to them using Laravel. Utilizes Discord HTTP webhooks without the need of a long-running process.

## Installation

You can install the package via composer:

```bash
composer require plytas/laravel-discord-interactions
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="discord-interactions-config"
```

This is the contents of the published config file:

```php
return [
    'application_id' => env('DISCORD_APPLICATION_ID'),
    'public_key' => env('DISCORD_PUBLIC_KEY'),
    'bot_token' => env('DISCORD_BOT_TOKEN'),

    'route' => [
        'path' => env('DISCORD_ROUTE', '/discord'),

        'middleware' => [
            'before' => [
                //ThrottleRequests::class,
            ],
            'after' => [

            ],
        ],
    ],
];
```

### Configuration

Create a new discord application at https://discord.com/developers/applications.
Copy the application id and public key from "General information" tab and bot token from "Bot" tab to your `.env` file.

### Interactions endpoint setup

For local development you can use services such as [expose](https://expose.dev) or [ngrok](https://ngrok.com/) to expose your local server to the internet.

In your discord application fill in `Interactions Endpoint URL` under "General information" with the url of your server followed by the path you set in the config file. By default it is `/discord`.

```
https://your-server-url.com/discord
```

When clicking the "Save Changes" button, Discord will send a couple `POST` request to the provided URL to verify the endpoint. One of them will intentionally contain an invalid signature. The package will automatically respond to these requests appropriately.

If the changes are saved successfully, you are good to go!

## Usage

### Creating a command

To create a new chat command, create a new class that implements the `Plytas\Discord\Contracts\DiscordCommand` interface.

```php
use Plytas\Discord\Contracts\DiscordCommand;
use Plytas\Discord\Data\DiscordInteraction;
use Plytas\Discord\Data\DiscordMessage;
use Plytas\Discord\Data\DiscordResponse;

class PingCommand implements DiscordCommand
{
    public function description(): string
    {
        return 'Replies with pong';
    }

    public function handle(DiscordInteraction $interaction): DiscordResponse
    {
        return $interaction->respondWithMessage(
            DiscordMessage::new()
                ->setContent('Pong!')
                ->ephemeral()
        );
    }
}
```

### Registering a command

You can register commands using `Plytas\Discord\DiscordCommandRegistry::setCommands` method. This can be done in the `boot()` method of a service provider.

```php
use Plytas\Discord\DiscordCommandRegistry;

DiscordCommandRegistry::setCommands([
    'ping' => PingCommand::class,
    
    // Nested commands
    'user' => [
        'role' => [
            'add' => AddRoleCommand::class,
            'remove' => RemoveRoleCommand::class,
        ],
    ],
]);
```

And finally call `php artisan discord:register-commands` to register the commands with Discord. It's recommended to run this command in a deployment script.

### Responding to interactions

Your commands will receive a `DiscordInteraction` object that contains information about the interaction. You can respond to the interaction directly by returning a `DiscordResponse` object.
`DidcordInteraction` object contains helper methods to create responses such as `respondWithMessage()`, `updateMessage()` and `showModal()`.

You must respond to interactions within **3** seconds. If you fail to do so, Discord will show a generic error message to the user. If you need more time to process the interaction, it is recommended to respond with a message that indicates that the command is still processing.
After processing is done, you can use `\Plytas\Discord\Facades\Discord::updateInteractionMessage()` in a `\Illuminate\Support\Facades\App::terminating()` callback to update the message.

```php
use Illuminate\Support\Facades\App;
use Plytas\Discord\Contracts\DiscordCommand;
use Plytas\Discord\Data\DiscordInteraction;
use Plytas\Discord\Data\DiscordMessage;
use Plytas\Discord\Data\DiscordResponse;
use Plytas\Discord\Facades\Discord;

class PingCommand implements DiscordCommand
{
    public function description(): string
    {
        return 'Replies with pong';
    }

    public function handle(DiscordInteraction $interaction): DiscordResponse
    {
        // Register a terminating callback to update the message after processing is done
        App::terminating(function () use ($interaction) {
            // Simulate a long-running process
            sleep(5);
        
            Discord::updateInteractionMessage(
                interaction: $interaction,
                message: DiscordMessage::new()
                    ->setContent('Pong!')
                    ->ephemeral()
            );
        });
    
        // Respond with a message that indicates that the command is still processing
        return $interaction->respondWithMessage(
            DiscordMessage::new()
                ->setContent('Calculating...')
                ->ephemeral() // Only the user who invoked the command can see the message
        );
    }
}
```

### Responding to component interactions

You can add components to your messages to allow users to interact with them.

```php
use Plytas\Discord\Components\ActionRow;
use Plytas\Discord\Components\Button;
use Plytas\Discord\Contracts\DiscordCommand;
use Plytas\Discord\Data\DiscordInteraction;
use Plytas\Discord\Data\DiscordMessage;
use Plytas\Discord\Data\DiscordResponse;
use Plytas\Discord\Enums\ButtonStyle;

class RockPaperScissorsCommand implements DiscordCommand
{
    public function description(): string
    {
        return 'Play rock-paper-scissors';
    }

    public function handle(DiscordInteraction $interaction): DiscordResponse
    {
        return $interaction->respondWithMessage(
            DiscordMessage::new()
                ->setContent('Select your move')
                ->addComponent(
                    component: ActionRow::new()
                        ->addComponent(
                            component: Button::new()
                                ->setCustomId('rock')
                                ->setLabel('Rock')
                                ->setEmoji('🪨')
                                ->setStyle(ButtonStyle::Primary)
                        )
                        ->addComponent(
                            component: Button::new()
                                ->setCustomId('paper')
                                ->setLabel('Paper')
                                ->setEmoji('📄')
                                ->setStyle(ButtonStyle::Primary)
                        )
                        ->addComponent(
                            component: Button::new()
                                ->setCustomId('scissors')
                                ->setLabel('Scissors')
                                ->setEmoji('✂️')
                                ->setStyle(ButtonStyle::Primary)
                        )
                )
                ->ephemeral() // Only the user who invoked the command can see the message
        );
    }
}
```

You can respond to component interactions by creating a new class that implements the `\Plytas\Discord\Contracts\DiscordComponentHandler` interface.

```php
use Illuminate\Support\Arr;
use Plytas\Discord\Contracts\DiscordComponentHandler;
use Plytas\Discord\Data\DiscordInteraction;
use Plytas\Discord\Data\DiscordMessage;
use Plytas\Discord\Data\DiscordResponse;

class RockPaperScissorsComponentHandler implements DiscordComponentHandler
{
    public function handle(DiscordInteraction $interaction): DiscordResponse
    {
        $playerSelection = $interaction->getMessageComponent()->custom_id;
        $botSelection = Arr::random(['rock', 'paper', 'scissors']);
        
        if ($playerSelection === $botSelection) {
            $message = 'It\'s a tie!';
        }
        
        $winningMoves = [
            'rock' => 'scissors',
            'paper' => 'rock',
            'scissors' => 'paper',
        ];
        
        if ($winningMoves[$playerSelection] === $botSelection) {
            $message = 'You win!';
        } else {
            $message = 'You lose!';
        }
        
        return $interaction->updateMessage(
            DiscordMessage::new()
                ->setContent($message)
                ->ephemeral()
        );
    }
}
```

### Registering component handlers

You can register component handlers using `Plytas\Discord\DiscordComponentRegistry::setComponentHandlers()` method. This method accepts an array of `component_id => DiscordComponentHandler class`. This can be done in the `boot()` method of a service provider.

```php
use Plytas\Discord\DiscordComponentRegistry;

DiscordComponentRegistry::setComponentHandlers([
    'rock' => RockPaperScissorsComponentHandler::class,
    'paper' => RockPaperScissorsComponentHandler::class,
    'scissors' => RockPaperScissorsComponentHandler::class,
]);
```

If you need more control over the component handler registration process, you can use `Plytas\Discord\DiscordComponentRegistry::handleComponentsUsing()` method.
This method accepts a closure that receives `Plytas\Discord\Data\DiscordInteraction` object and expects a `Plytas\Discord\Data\DiscordResponse` object to be returned.

Imagine that your components have a dynamic custom id that contains the name of the game. You can use the following code to handle the components:

```php
use Illuminate\Support\Str;
use Plytas\Discord\Data\DiscordInteraction;
use Plytas\Discord\Events\ComponentHandlerRegistered;

DiscordComponentRegistry::handleComponentsUsing(function(DiscordInteraction $interaction)  {
    $gameName = Str::afterLast($interaction->getMessageComponent()->custom_id, ':');
    
    return match ($gameName) {
        'rock-paper-scissors' => (new RockPaperScissorsComponentHandler)->handle($interaction),
        'coin-flip' => (new CoinFlipComponentHandler)->handle($interaction),
        'default' => $interaction->updateMessage(
            DiscordMessage::new()
                ->setContent('Invalid game')
                ->ephemeral()
        );
    };
});
```

> [!WARNING]
> Not all components and API features are supported yet. This package started as a personal project and is still in development. If you need a specific feature, feel free to open an issue or a pull request.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Plytas](https://github.com/Plytas)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
