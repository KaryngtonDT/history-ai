<?php

declare(strict_types=1);

namespace App\Domain\Chat;

final class ChatOrchestrator
{
    public function buildPrompt(ChatContext $context): ChatPrompt
    {
        $lines = [
            'Answer the question using only the document excerpts below.',
            '',
            'Question:',
            $context->question()->value(),
            '',
            'Excerpts:',
        ];

        foreach ($context->sources()->sources() as $index => $source) {
            $lines[] = sprintf(
                '[%d] %s (score: %.4f)',
                $index + 1,
                $source->text(),
                $source->score()->value(),
            );
        }

        if ($context->sources()->isEmpty()) {
            $lines[] = '(no excerpts available)';
        }

        return new ChatPrompt(implode("\n", $lines));
    }
}
