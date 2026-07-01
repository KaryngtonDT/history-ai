<?php

declare(strict_types=1);

namespace App\Domain\Telemetry;

use App\Domain\Telemetry\Exception\InvalidPipelineTelemetryException;

final readonly class ProviderUsageCollection
{
    /** @var list<ProviderUsage> */
    private array $usages;

    /**
     * @param list<ProviderUsage> $usages
     */
    public function __construct(array $usages = [])
    {
        $indexed = [];

        foreach ($usages as $usage) {
            $key = $usage->stage().':'.$usage->providerId();

            if (isset($indexed[$key])) {
                throw new InvalidPipelineTelemetryException(sprintf(
                    'Duplicate provider usage for "%s".',
                    $key,
                ));
            }

            $indexed[$key] = $usage;
        }

        $this->usages = array_values($indexed);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<ProviderUsage>
     */
    public function all(): array
    {
        return $this->usages;
    }

    public function count(): int
    {
        return count($this->usages);
    }

    public function append(ProviderUsage $usage): self
    {
        foreach ($this->usages as $existing) {
            if ($existing->stage() === $usage->stage() && $existing->providerId() === $usage->providerId()) {
                return new self(array_map(
                    static fn (ProviderUsage $entry): ProviderUsage => $entry->stage() === $usage->stage()
                        && $entry->providerId() === $usage->providerId()
                        ? $entry->merge($usage)
                        : $entry,
                    $this->usages,
                ));
            }
        }

        return new self([...$this->usages, $usage]);
    }

    public function topByInvocations(): ?ProviderUsage
    {
        if ([] === $this->usages) {
            return null;
        }

        $sorted = $this->usages;
        usort(
            $sorted,
            static fn (ProviderUsage $left, ProviderUsage $right): int => $right->invocationCount() <=> $left->invocationCount(),
        );

        return $sorted[0];
    }

    /**
     * @return list<ProviderUsage>
     */
    public function forStage(string $stage): array
    {
        return array_values(array_filter(
            $this->usages,
            static fn (ProviderUsage $usage): bool => $usage->stage() === $stage,
        ));
    }
}
