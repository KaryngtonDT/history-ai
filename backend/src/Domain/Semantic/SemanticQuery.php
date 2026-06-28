<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

use App\Domain\Semantic\Exception\InvalidSemanticQueryException;

final readonly class SemanticQuery
{
    private const int MAX_LENGTH = 500;

    private string $value;

    public function __construct(string $raw)
    {
        $trimmed = trim($raw);

        if ('' === $trimmed) {
            throw new InvalidSemanticQueryException('Semantic query cannot be empty.');
        }

        if (strlen($trimmed) > self::MAX_LENGTH) {
            throw new InvalidSemanticQueryException(
                sprintf('Semantic query cannot exceed %d characters.', self::MAX_LENGTH),
            );
        }

        $this->value = $trimmed;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
