<?php
declare(strict_types=1);

namespace App\Services;

use App\Chat\ChatProviderInterface;
use App\Repositories\ConversationRepository;
use App\Repositories\MessageRepository;

final class ChatService
{
    public function __construct(
        private ConversationRepository $conversations,
        private MessageRepository $messages,
        private ChatProviderInterface $provider,
        private AuditService $audit
    ) {
    }

    public function beginConversation(int $userId, string $tierLabel, string $ip, string $userAgent): int
    {
        return $this->conversations->create($userId, $tierLabel, $ip, $userAgent);
    }

    public function reply(int $conversationId, int $userId, string $message, array $context, string $ip): string
    {
        $this->messages->add($conversationId, 'user', $message);
        $reply = $this->provider->generateReply($message, $context);
        $this->messages->add($conversationId, 'assistant', $reply);

        $this->audit->log('user', $userId, 'chat_message_recorded', [
            'conversation_id' => $conversationId,
            'message_length' => strlen($message),
        ], $ip);

        return $reply;
    }
}
