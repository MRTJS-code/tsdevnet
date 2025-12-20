<?php
declare(strict_types=1);

class Mailer
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config['mail'] ?? [];
    }

    public function sendMagicLink(string $toEmail, string $toName, string $link, int $ttlSeconds): bool
    {
        $subject = 'Your secure login link';
        $body = "Hi {$toName},\n\n"
              . "Here is your secure login link (valid for " . floor($ttlSeconds / 60) . " minutes):\n"
              . "{$link}\n\n"
              . "If you didn’t request this, ignore this email.\n";

        return $this->send($toEmail, $toName, $subject, $body);
    }

    public function send(string $toEmail, string $toName, string $subject, string $body): bool
    {
        if (class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            return $this->sendWithPHPMailer($toEmail, $toName, $subject, $body);
        }

        // Fallback to PHP mail() if PHPMailer is not installed.
        $headers = [
            'From: ' . ($this->config['from_name'] ?? 'Portal') . ' <' . ($this->config['from_email'] ?? 'noreply@example.com') . '>',
            'Reply-To: ' . ($this->config['from_email'] ?? 'noreply@example.com'),
        ];
        return mail($toEmail, $subject, $body, implode("\r\n", $headers));
    }

    private function sendWithPHPMailer(string $toEmail, string $toName, string $subject, string $body): bool
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'] ?? 'localhost';
            $mail->Port = (int)($this->config['smtp_port'] ?? 25);
            if (!empty($this->config['smtp_secure'])) {
                $mail->SMTPSecure = $this->config['smtp_secure'];
            }
            $mail->SMTPAuth = !empty($this->config['smtp_username']);
            if ($mail->SMTPAuth) {
                $mail->Username = $this->config['smtp_username'];
                $mail->Password = $this->config['smtp_password'];
            }

            $mail->setFrom($this->config['from_email'] ?? 'noreply@example.com', $this->config['from_name'] ?? 'Portal');
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $body;
            $mail->isHTML(false);

            return $mail->send();
        } catch (Throwable $e) {
            return false;
        }
    }
}
