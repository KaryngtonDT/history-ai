<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

use App\Domain\Semantic\Exception\InvalidEmbeddingVectorException;

final readonly class EmbeddingVector
{
    /** @var list<float> */
    private array $values;

    /**
     * @param list<float|int> $values
     */
    public function __construct(array $values)
    {
        if ([] === $values) {
            throw new InvalidEmbeddingVectorException('Embedding vector cannot be empty.');
        }

        /** @var list<float> $normalized */
        $normalized = [];

        foreach ($values as $index => $value) {
            if (!is_int($value) && !is_float($value)) {
                throw new InvalidEmbeddingVectorException(
                    sprintf('Embedding vector values must be numeric floats, got invalid value at index %d.', $index),
                );
            }

            $normalized[] = (float) $value;
        }

        $this->values = $normalized;
    }

    /**
     * @return list<float>
     */
    public function values(): array
    {
        return $this->values;
    }

    public function dimension(): int
    {
        return count($this->values);
    }

    public function equals(self $other): bool
    {
        return $this->values === $other->values;
    }
}
