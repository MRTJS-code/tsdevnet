<?php
declare(strict_types=1);

namespace App\Support;

use PHPMailer\PHPMailer\PHPMailer;
use Throwable;

final class Mailer
{
    private array $mailConfig;

    public function __construct(array $config)
    {
        $this->mailConfig = $config['mail'] ?? [];
    }

    public function sendMagicLink(string $toEmail, string $toName, string $link, int $ttlSeconds): bool
    {
        $subject = 'Your secure login link';
        $body = "Hi {$toName},\n\n"
            . 'Here is your secure login link (valid for ' . floor($ttlSeconds / 60) . " minutes):\n"
            . "{$link}\n\n"
            . "If you did not request this link, you can ignore this message.\n";

        return $this->send($toEmail, $toName, $subject, $body);
    }

    public function send(string $toEmail, string $toName, string $subject, string $body): bool
    {
        if (class_exists(PHPMailer::class)) {
            return $this->sendWithPhpMailer($toEmail, $toName, $subject, $body);
        }

        $headers = [
            'From: ' . ($this->mailConfig['from_name'] ?? 'Portal') . ' <' . ($this->mailConfig['from_email'] ?? 'noreply@example.com') . '>',
            'Reply-To: ' . ($this->mailConfig['from_email'] ?? 'noreply@example.com'),
        ];

        return mail($toEmail, $subject, $body, implode("\r\n", $headers));
    }

    private function sendWithPhpMailer(string $toEmail, string $toName, string $subject, string $body): bool
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $this->mailConfig['smtp_host'] ?? 'localhost';
            $mail->Port = (int) ($this->mailConfig['smtp_port'] ?? 25);
            $mail->SMTPAuth = !empty($this->mailConfig['smtp_username']);

            if (!empty($this->mailConfig['smtp_secure'])) {
                $mail->SMTPSecure = $this->mailConfig['smtp_secure'];
            }

            if ($mail->SMTPAuth) {
                $mail->Username = $this->mailConfig['smtp_username'];
                $mail->Password = $this->mailConfig['smtp_password'];
            }

            $mail->setFrom($this->mailConfig['from_email'] ?? 'noreply@example.com', $this->mailConfig['from_name'] ?? 'Portal');
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $body;
            $mail->isHTML(false);

            return $mail->send();
        } catch (Throwable) {
            return false;
        }
    }
}

