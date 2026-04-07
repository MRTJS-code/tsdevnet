<?php
declare(strict_types=1);

namespace App\Chat;

interface ChatProviderInterface
{
    public function generateReply(string $message, array $context = []): string;
}

