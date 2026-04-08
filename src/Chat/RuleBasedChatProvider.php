<?php
declare(strict_types=1);

namespace App\Chat;

use App\Repositories\AssistantKnowledgeRepository;

final class RuleBasedChatProvider implements ChatProviderInterface
{
    public function __construct(private AssistantKnowledgeRepository $knowledge)
    {
    }

    public function generateReply(string $message, array $context = []): string
    {
        $lower = strtolower($message);
        $tier = (string) ($context['status'] ?? 'pending');

        foreach ($this->knowledge->listActiveForTier($tier) as $entry) {
            $triggerType = (string) $entry['trigger_type'];
            $triggerValue = strtolower((string) $entry['trigger_value']);
            if ($triggerType === 'exact' && $lower === $triggerValue) {
                return (string) $entry['response_text'];
            }
            if ($triggerType === 'contains' && $triggerValue !== '' && str_contains($lower, $triggerValue)) {
                return (string) $entry['response_text'];
            }
        }

        return 'This is a gated demo response. The assistant knowledge base is editable in admin, the conversation stays server-side, and the current rule-based layer keeps a clean seam for later AI integration.';
    }
}
