<?php
declare(strict_types=1);

namespace App\Support;

final class Json
{
    public static function encodeArray(?string $json): ?string
    {
        $trimmed = trim((string) $json);
        if ($trimmed === '') {
            return null;
        }

        $decoded = json_decode($trimmed, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return null;
        }

        return json_encode($decoded, JSON_UNESCAPED_SLASHES);
    }
}
