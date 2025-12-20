<?php
declare(strict_types=1);

class Auth
{
    private PDO $pdo;
    private array $config;

    public function __construct(PDO $pdo, array $config)
    {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    public function currentUser(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $cache = $stmt->fetch();
        return $cache ?: null;
    }

    public function requireLogin(): void
    {
        if (!$this->currentUser()) {
            header('Location: /login.php');
            exit;
        }
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public function createUser(array $data): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$data['email']]);
        $existing = $stmt->fetch();
        if ($existing) {
            return (int)$existing['id'];
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, company, role_type, linkedin_url, hiring_for, consent_at, status, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['company'],
            $data['role_type'],
            $data['linkedin_url'],
            $data['hiring_for'],
            $data['consent_at'],
            'pending',
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function createMagicToken(int $userId, string $ip, string $ua): array
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $ttl = $this->config['magic_link_ttl'] ?? 900;
        $expiresAt = date('Y-m-d H:i:s', time() + $ttl);

        $stmt = $this->pdo->prepare(
            'INSERT INTO access_tokens (user_id, token_hash, expires_at, created_at, ip_address, user_agent) VALUES (?, ?, ?, NOW(), ?, ?)'
        );
        $stmt->execute([$userId, $hash, $expiresAt, $ip, $ua]);

        return ['token' => $token, 'expires_at' => $expiresAt, 'ttl' => $ttl];
    }

    public function verifyAndLogin(string $token): ?int
    {
        $hash = hash('sha256', $token);
        $stmt = $this->pdo->prepare(
            'SELECT at.id, at.user_id, at.expires_at, at.used_at, u.status 
             FROM access_tokens at 
             JOIN users u ON u.id = at.user_id 
             WHERE at.token_hash = ? LIMIT 1'
        );
        $stmt->execute([$hash]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        if ($row['used_at'] !== null) {
            return null;
        }
        if (strtotime($row['expires_at']) < time()) {
            return null;
        }
        if ($row['status'] === 'blocked') {
            return null;
        }

        $this->pdo->beginTransaction();
        $upd = $this->pdo->prepare('UPDATE access_tokens SET used_at = NOW() WHERE id = ?');
        $upd->execute([$row['id']]);
        $login = $this->pdo->prepare('UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = ?');
        $login->execute([$row['user_id']]);
        $this->pdo->commit();

        $_SESSION['user_id'] = (int)$row['user_id'];
        return (int)$row['user_id'];
    }
}
