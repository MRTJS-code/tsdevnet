<?php
declare(strict_types=1);

namespace App\Http;

final class Response
{
    public static function redirect(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }

    public static function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function abort(int $status, string $message): void
    {
        http_response_code($status);
        echo $message;
        exit;
    }
}

