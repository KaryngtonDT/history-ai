<?php

declare(strict_types=1);

namespace App\Domain\Learning;

use App\Domain\Learning\Exception\InvalidLearningProfileException;

final readonly class LearningSignal
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        private LearningSignalId $id,
        private LearningSignalType $type,
        private \DateTimeImmutable $recordedAt,
        private array $context,
    ) {
        if ('' === trim($context['summary'] ?? '') && !array_key_exists('value', $context)) {
            throw new InvalidLearningProfileException('Learning signal context must include summary or value.');
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function record(
        LearningSignalType $type,
        array $context,
        ?LearningSignalId $id = null,
        ?\DateTimeImmutable $recordedAt = null,
    ): self {
        return new self(
            $id ?? LearningSignalId::generate(),
            $type,
            $recordedAt ?? new \DateTimeImmutable(),
            $context,
        );
    }

    public function id(): LearningSignalId
    {
        return $this->id;
    }

    public function type(): LearningSignalType
    {
        return $this->type;
    }

    public function recordedAt(): \DateTimeImmutable
    {
        return $this->recordedAt;
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
