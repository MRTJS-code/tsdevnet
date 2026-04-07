<?php
declare(strict_types=1);

namespace App\Chat;

final class RuleBasedChatProvider implements ChatProviderInterface
{
    public function generateReply(string $message, array $context = []): string
    {
        $lower = strtolower($message);

        if (str_contains($lower, 'role') || str_contains($lower, 'scope')) {
            return 'This demo assistant can summarise role scope, likely delivery accountabilities, and how Tony would shape an operating model around the brief. Approved access unlocks deeper recruiter-facing responses.';
        }

        if (str_contains($lower, 'skills') || str_contains($lower, 'stack')) {
            return "Tony typically operates across business systems, data platforms, delivery governance, architecture alignment, and practical modernisation. This Phase 1 assistant is intentionally rule-based and server-side only.";
        }

        if (str_contains($lower, 'lead') || str_contains($lower, 'team')) {
            return 'The leadership angle here is pragmatic: clear decision rights, useful metrics, credible governance, and delivery structures that fit the size of the organisation rather than enterprise theatre.';
        }

        return 'This is a gated demo response. The current architecture stores the conversation server-side, applies access controls and rate limits, and leaves a clean seam for later Azure OpenAI integration.';
    }
}

