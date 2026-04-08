<?php
declare(strict_types=1);

namespace App\Support;

final class Util
{
    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function baseUrl(array $config): string
    {
        return rtrim((string) ($config['app_url'] ?? ''), '/');
    }

    public static function isDev(array $config): bool
    {
        return ($config['app_env'] ?? 'prod') === 'dev';
    }

    public static function sanitizeEmail(string $email): string
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL) ?: '';
    }

    public static function validateEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function clientIp(): string
    {
        $candidates = [
            $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if ($candidate && filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }

        return '0.0.0.0';
    }

    public static function userAgent(): string
    {
        return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'), 0, 255);
    }

    public static function tierFromStatus(string $status, array $config): array
    {
        if ($status === 'approved') {
            return [
                'label' => 'Approved recruiter access',
                'limit' => (int) ($config['rate_limits']['chat_approved_per_day'] ?? 50),
            ];
        }

        return [
            'label' => 'Pending demo access',
            'limit' => (int) ($config['rate_limits']['chat_pending_per_day'] ?? 5),
        ];
    }

    public static function sectionLabel(string $sectionKey): string
    {
        return match ($sectionKey) {
            'hero' => 'Hero',
            'summary_cards' => 'Summary Cards',
            'about' => 'About',
            'achievements' => 'Achievements',
            'technology_tags' => 'Technology Tags',
            'operating_approach' => 'Operating Approach',
            'contact_cta' => 'Contact CTA',
            default => ucwords(str_replace('_', ' ', $sectionKey)),
        };
    }
}
