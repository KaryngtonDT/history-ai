<?php

declare(strict_types=1);

namespace App\Domain\AI;

use App\Domain\AI\Exception\InvalidAIEngineException;

final readonly class AIEngineId
{
    public function __construct(public string $value)
    {
        if (!self::isValid($value)) {
            throw new InvalidAIEngineException('AI engine id must be a non-empty slug.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public static function isValid(string $value): bool
    {
        $trimmed = trim($value);

        return '' !== $trimmed && 1 === preg_match('/^[a-z][a-z0-9_-]*$/', $trimmed);
    }
}
