<?php

namespace Plytas\Discord\RateLimiting;

use Psr\Http\Message\RequestInterface;

/**
 * Discord puts requests in one rate limit bucket when they have the same route template and the same major
 * parameter. The major parameters are a channel id, a guild id, or a webhook id with its token.
 */
final readonly class DiscordRoute
{
    private const MAJOR_RESOURCES = ['channels', 'guilds', 'webhooks'];

    public function __construct(
        public string $template,
        public string $majorParameter,
    ) {}

    public static function fromRequest(RequestInterface $request): self
    {
        $path = preg_replace('#^/api/v\d+#', '', $request->getUri()->getPath()) ?? '';
        $segments = array_values(array_filter(explode('/', $path), static fn (string $segment): bool => $segment !== ''));

        $majorLength = match (true) {
            ! in_array($segments[0] ?? null, self::MAJOR_RESOURCES, true) => 0,
            $segments[0] === 'webhooks' => 3,
            default => 2,
        };

        $majorParameter = implode('/', array_slice($segments, 0, $majorLength));

        $templateSegments = [];

        foreach ($segments as $index => $segment) {
            $templateSegments[] = match (true) {
                $index > 0 && $index < $majorLength => '{major}',
                ctype_digit($segment) => '{id}',
                default => $segment,
            };
        }

        return new self(
            template: $request->getMethod().' /'.implode('/', $templateSegments),
            majorParameter: $majorParameter,
        );
    }
}
