<?php

declare(strict_types=1);

namespace App\Domain\Learning;

use App\Domain\Learning\Exception\InvalidLearningProfileException;

final readonly class LearningSignalId
{
    public function __construct(public string $value)
    {
        if (!LearningProfileId::isValid($value)) {
            throw new InvalidLearningProfileException('Learning signal id must be a valid UUID.');
        }
    }

    public static function generate(): self
    {
        return new self(LearningProfileId::generate()->value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
