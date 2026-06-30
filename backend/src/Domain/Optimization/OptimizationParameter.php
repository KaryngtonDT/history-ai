<?php

declare(strict_types=1);

namespace App\Domain\Optimization;

use App\Domain\Optimization\Exception\InvalidExecutionOptimizationException;

final readonly class OptimizationParameter
{
    public function __construct(
        private string $key,
        private string $value,
    ) {
        if ('' === trim($this->key)) {
            throw new InvalidExecutionOptimizationException('Optimization parameter key must not be empty.');
        }

        if ('' === trim($this->value)) {
            throw new InvalidExecutionOptimizationException('Optimization parameter value must not be empty.');
        }
    }

    public static function create(string $key, string $value): self
    {
        return new self($key, $value);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function value(): string
    {
        return $this->value;
    }
}
