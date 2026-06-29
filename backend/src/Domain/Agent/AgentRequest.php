<?php

declare(strict_types=1);

namespace App\Domain\Agent;

use App\Domain\Agent\Exception\InvalidAgentPlanException;

final readonly class AgentRequest
{
    private const int MIN_LENGTH = 1;

    private const int MAX_LENGTH = 2000;

    private string $question;

    public function __construct(string $rawQuestion)
    {
        $trimmed = trim($rawQuestion);

        if (strlen($trimmed) < self::MIN_LENGTH) {
            throw new InvalidAgentPlanException('Agent question cannot be empty.');
        }

        if (strlen($trimmed) > self::MAX_LENGTH) {
            throw new InvalidAgentPlanException(
                sprintf('Agent question cannot exceed %d characters.', self::MAX_LENGTH),
            );
        }

        $this->question = $trimmed;
    }

    public function question(): string
    {
        return $this->question;
    }

    public function equals(self $other): bool
    {
        return $this->question === $other->question;
    }
}
