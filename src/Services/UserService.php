<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Support\Mailer;
use App\Support\Util;

final class UserService
{
    public function __construct(
        private UserRepository $users,
        private MagicLinkService $magicLinks,
        private Mailer $mailer,
        private AuditService $audit,
        private array $config
    ) {
    }

    public function createOrReuseAccessRequest(array $data, string $ip, string $userAgent): array
    {
        $existing = $this->users->findByEmail($data['email']);
        if ($existing && in_array($existing['status'], ['blocked', 'rejected'], true)) {
            $this->audit->log('user', (int) $existing['id'], 'restricted_access_request_attempted', [
                'email' => $data['email'],
                'status' => $existing['status'],
            ], $ip);

            return [
                'user' => $existing,
                'magic_link' => null,
            ];
        }

        $userId = $existing ? (int) $existing['id'] : $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'company' => $data['company'],
            'role_type' => $data['role_type'],
            'linkedin_url' => $data['linkedin_url'],
            'hiring_for' => $data['hiring_for'],
            'consent_at' => date('Y-m-d H:i:s'),
            'status' => 'pending',
        ]);

        $user = $existing ?: $this->users->findById($userId);
        $token = $this->magicLinks->issueToken($userId, $ip, $userAgent);
        $link = Util::baseUrl($this->config) . '/verify.php?token=' . urlencode($token['token']);
        $this->mailer->sendMagicLink($data['email'], $data['name'], $link, $token['ttl']);

        $this->audit->log('user', $userId, $existing ? 'access_request_reused' : 'access_request_created', [
            'email' => $data['email'],
            'company' => $data['company'],
            'role_type' => $data['role_type'],
        ], $ip);

        return [
            'user' => $user,
            'magic_link' => Util::isDev($this->config) ? $link : null,
        ];
    }

    public function requestLoginLink(string $email, string $ip, string $userAgent): ?string
    {
        $user = $this->users->findByEmail($email);
        if (!$user || in_array($user['status'], ['blocked', 'rejected'], true)) {
            $action = $user ? 'login_link_requested_restricted' : 'login_link_requested_unknown';
            $this->audit->log('user', $user ? (int) $user['id'] : null, $action, ['email' => $email], $ip);
            return null;
        }

        $token = $this->magicLinks->issueToken((int) $user['id'], $ip, $userAgent);
        $link = Util::baseUrl($this->config) . '/verify.php?token=' . urlencode($token['token']);
        $this->mailer->sendMagicLink($email, $user['name'], $link, $token['ttl']);
        $this->audit->log('user', (int) $user['id'], 'login_link_requested', ['email' => $email], $ip);

        return Util::isDev($this->config) ? $link : null;
    }
}
