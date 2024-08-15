<?php

namespace Plytas\Discord\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Plytas\Discord\Exceptions\InvalidConfigurationException;
use Throwable;

class VerifyDiscordSignatureMiddleware
{
    /**
     * @template TNext
     *
     * @param  Closure(Request): TNext  $next
     * @return TNext
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $signature = $request->header('X-Signature-Ed25519');
        $timestamp = $request->header('X-Signature-Timestamp');

        if (! $signature || ! $timestamp || ! $this->verify($signature, $timestamp, $request->getContent())) {
            throw new AuthenticationException;
        }

        $applicationId = config('discord-interactions.application_id');

        if (! is_string($applicationId)) {
            throw new InvalidConfigurationException('discord-interactions.application_id');
        }

        if (! $request->string('application_id')->exactly($applicationId)) {
            throw new AuthorizationException;
        }

        return $next($request);
    }

    private function verify(string $signature, string $timestamp, string $content): bool
    {
        try {
            $binarySignature = sodium_hex2bin($signature);
            $publicKey = config('discord-interactions.public_key');

            if (! is_string($publicKey)) {
                throw new InvalidConfigurationException('discord-interactions.public_key');
            }

            $binaryKey = sodium_hex2bin($publicKey);

            if ($binarySignature === '' || $binaryKey === '') {
                return false;
            }

            return sodium_crypto_sign_verify_detached($binarySignature, $timestamp.$content, $binaryKey);
        } catch (Throwable) {
            return false;
        }
    }
}
