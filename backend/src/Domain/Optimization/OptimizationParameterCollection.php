<?php

declare(strict_types=1);

namespace App\Domain\Optimization;

use App\Domain\Optimization\Exception\InvalidExecutionOptimizationException;

final readonly class OptimizationParameterCollection
{
    /** @var list<OptimizationParameter> */
    private array $parameters;

    /**
     * @param list<OptimizationParameter> $parameters
     */
    public function __construct(array $parameters)
    {
        $seen = [];

        foreach ($parameters as $parameter) {
            if (isset($seen[$parameter->key()])) {
                throw new InvalidExecutionOptimizationException(sprintf(
                    'Duplicate optimization parameter "%s".',
                    $parameter->key(),
                ));
            }

            $seen[$parameter->key()] = true;
        }

        $this->parameters = array_values($parameters);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<OptimizationParameter>
     */
    public function all(): array
    {
        return $this->parameters;
    }

    public function count(): int
    {
        return count($this->parameters);
    }

    public function isEmpty(): bool
    {
        return [] === $this->parameters;
    }

    public function get(string $key): ?OptimizationParameter
    {
        foreach ($this->parameters as $parameter) {
            if ($parameter->key() === $key) {
                return $parameter;
            }
        }

        return null;
    }

    public function valueFor(string $key): ?string
    {
        return $this->get($key)?->value();
    }
}
