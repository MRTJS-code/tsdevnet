<?php
declare(strict_types=1);

namespace App\Support;

final class Turnstile
{
    public static function verify(string $secretKey, string $token, ?string $ip = null): bool
    {
        if ($secretKey === '' || $token === '') {
            return false;
        }

        $payload = http_build_query([
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => $ip,
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $payload,
                'timeout' => 5,
            ],
        ]);

        $result = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $context);
        if ($result === false) {
            return false;
        }

        $decoded = json_decode($result, true);
        return is_array($decoded) && !empty($decoded['success']);
    }
}

