<?php

declare(strict_types=1);

namespace App\Domain\History;

use App\Domain\History\Exception\InvalidExecutionHistoryException;

final readonly class ExecutionVersionCollection
{
    /** @var list<ExecutionVersion> */
    private array $versions;

    /**
     * @param list<ExecutionVersion> $versions
     */
    public function __construct(array $versions = [])
    {
        $seen = [];
        $ordered = [];

        foreach ($versions as $version) {
            $number = $version->versionNumber();

            if (isset($seen[$number])) {
                throw new InvalidExecutionHistoryException(sprintf(
                    'Duplicate execution version number "%d".',
                    $number,
                ));
            }

            $seen[$number] = true;
            $ordered[] = $version;
        }

        usort(
            $ordered,
            static fn (ExecutionVersion $left, ExecutionVersion $right): int => $left->versionNumber() <=> $right->versionNumber(),
        );

        $this->versions = $ordered;
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<ExecutionVersion>
     */
    public function all(): array
    {
        return $this->versions;
    }

    public function count(): int
    {
        return count($this->versions);
    }

    public function isEmpty(): bool
    {
        return [] === $this->versions;
    }

    public function append(ExecutionVersion $version): self
    {
        if ($this->hasVersionNumber($version->versionNumber())) {
            throw new InvalidExecutionHistoryException(sprintf(
                'Version number "%d" already exists.',
                $version->versionNumber(),
            ));
        }

        return new self([...$this->versions, $version]);
    }

    public function hasVersionNumber(int $versionNumber): bool
    {
        foreach ($this->versions as $version) {
            if ($version->versionNumber() === $versionNumber) {
                return true;
            }
        }

        return false;
    }

    public function version(int $versionNumber): ExecutionVersion
    {
        foreach ($this->versions as $version) {
            if ($version->versionNumber() === $versionNumber) {
                return $version;
            }
        }

        throw new InvalidExecutionHistoryException(sprintf(
            'Execution version "%d" not found.',
            $versionNumber,
        ));
    }

    public function latest(): ?ExecutionVersion
    {
        if ($this->isEmpty()) {
            return null;
        }

        return $this->versions[array_key_last($this->versions)];
    }

    public function nextVersionNumber(): int
    {
        $latest = $this->latest();

        return null === $latest ? 1 : $latest->versionNumber() + 1;
    }
}
