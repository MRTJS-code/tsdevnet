<?php
declare(strict_types=1);

namespace App\Support;

final class Config
{
    public static function merge(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = self::merge($base[$key], $value);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }
}

