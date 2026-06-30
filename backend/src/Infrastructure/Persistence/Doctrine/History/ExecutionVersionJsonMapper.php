<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\History;

use App\Application\History\ExecutionVersionSnapshot;
use App\Domain\History\ExecutionHistory;
use App\Domain\History\ExecutionVersion;
use JsonException;

final class ExecutionVersionJsonMapper
{
    /**
     * @return list<array<string, mixed>>
     */
    public function snapshotsToArray(array $snapshots): array
    {
        return array_map(
            static fn (ExecutionVersionSnapshot $snapshot): array => $snapshot->toPayload(),
            $snapshots,
        );
    }

    /**
     * @param list<array<string, mixed>> $payloads
     *
     * @return list<ExecutionVersionSnapshot>
     */
    public function snapshotsFromArray(array $payloads): array
    {
        $snapshots = [];

        foreach ($payloads as $payload) {
            if (!is_array($payload)) {
                continue;
            }

            try {
                $snapshots[] = ExecutionVersionSnapshot::fromPayload($payload);
            } catch (\Throwable) {
                continue;
            }
        }

        usort(
            $snapshots,
            static fn (ExecutionVersionSnapshot $left, ExecutionVersionSnapshot $right): int => $left->version->versionNumber() <=> $right->version->versionNumber(),
        );

        return $snapshots;
    }

    /**
     * @return list<ExecutionVersion>
     */
    public function versionsFromJson(string $json): array
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (!is_array($decoded)) {
            return [];
        }

        $versions = [];

        foreach ($this->snapshotsFromArray($decoded) as $snapshot) {
            $versions[] = $snapshot->version;
        }

        return $versions;
    }
}
