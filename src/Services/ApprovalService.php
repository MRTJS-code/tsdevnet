<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ConversationRepository;
use App\Repositories\MessageRepository;
use App\Repositories\UserRepository;

final class ApprovalService
{
    public function __construct(
        private UserRepository $users,
        private ConversationRepository $conversations,
        private MessageRepository $messages,
        private AuditService $audit
    ) {
    }

    public function pendingUsers(): array
    {
        return $this->users->listRecentByStatus('pending');
    }

    public function userDetail(int $userId): ?array
    {
        $user = $this->users->findById($userId);
        if (!$user) {
            return null;
        }

        return [
            'user' => $user,
            'conversations' => $this->conversations->listByUserId($userId),
            'messages' => $this->messages->recentForUser($userId),
        ];
    }

    public function changeStatus(int $userId, string $status, string $ip): void
    {
        $this->users->updateStatus($userId, $status);
        $this->audit->log('admin', null, 'user_status_changed', ['user_id' => $userId, 'status' => $status], $ip);
    }

    public function updateNotes(int $userId, string $notes, string $ip): void
    {
        $this->users->updateNotes($userId, $notes);
        $this->audit->log('admin', null, 'user_notes_updated', ['user_id' => $userId], $ip);
    }
}

