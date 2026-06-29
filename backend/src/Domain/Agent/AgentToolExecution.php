<?php

declare(strict_types=1);

namespace App\Domain\Agent;

final readonly class AgentToolExecution
{
    public function __construct(
        private AgentTool $tool,
        private string $question,
        private string $contentId,
        private ?string $conversationId = null,
    ) {
    }

    public function tool(): AgentTool
    {
        return $this->tool;
    }

    public function question(): string
    {
        return $this->question;
    }

    public function contentId(): string
    {
        return $this->contentId;
    }

    public function conversationId(): ?string
    {
        return $this->conversationId;
    }

    public function equals(self $other): bool
    {
        return $this->tool === $other->tool
            && $this->question === $other->question
            && $this->contentId === $other->contentId
            && $this->conversationId === $other->conversationId;
    }
}
