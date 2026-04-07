<?php
declare(strict_types=1);

namespace App\Support;

use App\Http\Response;

final class Security
{
    public static function sendHeaders(array $config): void
    {
        header('Content-Type: text/html; charset=UTF-8');
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

        $baseUrl = Util::baseUrl($config);
        $host = parse_url($baseUrl, PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $connectSources = ["'self'"];
        if ($host) {
            $connectSources[] = 'https://' . $host;
        }

        $csp = [
            "default-src 'self'",
            "base-uri 'self'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "img-src 'self' data:",
            "font-src 'self'",
            'connect-src ' . implode(' ', array_unique($connectSources)),
            "style-src 'self' 'unsafe-inline'",
            "script-src 'self' https://challenges.cloudflare.com",
            "frame-src https://challenges.cloudflare.com",
        ];

        header('Content-Security-Policy: ' . implode('; ', $csp));
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        return is_string($token) && $sessionToken !== '' && hash_equals($sessionToken, $token);
    }

    public static function enforceSameOrigin(array $config): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
        if ($origin === '') {
            return;
        }

        $expectedHost = parse_url(Util::baseUrl($config), PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? '');
        $actualHost = parse_url($origin, PHP_URL_HOST);
        if ($expectedHost !== '' && $actualHost !== $expectedHost) {
            Response::abort(403, 'Forbidden');
        }
    }
}
