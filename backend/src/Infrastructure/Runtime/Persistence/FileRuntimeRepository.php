<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Persistence;

use App\Domain\Runtime\RuntimeConfiguration;
use App\Domain\Runtime\RuntimeExecution;
use App\Domain\Runtime\RuntimeRepositoryInterface;
use App\Infrastructure\Storage\JsonFileStore;

final class FileRuntimeRepository implements RuntimeRepositoryInterface
{
    private const string CONFIG_FILE = 'configuration.json';
    private const string EXECUTIONS_FILE = 'executions.json';
    private const string REPORTS_FILE = 'validation-reports.json';

    public function __construct(private readonly JsonFileStore $store)
    {
    }

    public function getConfiguration(): RuntimeConfiguration
    {
        $data = $this->store->read(self::CONFIG_FILE);

        return RuntimeConfiguration::fromArray(is_array($data) ? $data : []);
    }

    public function saveConfiguration(RuntimeConfiguration $configuration): void
    {
        $this->store->write(self::CONFIG_FILE, $configuration->toArray());
    }

    public function listExecutions(): array
    {
        $data = $this->store->read(self::EXECUTIONS_FILE);

        if (!is_array($data)) {
            return [];
        }

        $executions = [];

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            $executions[] = new RuntimeExecution(
                id: (string) ($item['id'] ?? ''),
                engineId: (string) ($item['engineId'] ?? ''),
                capability: \App\Domain\Runtime\RuntimeCapability::from((string) ($item['capability'] ?? 'speech_to_text')),
                status: \App\Domain\Runtime\RuntimeStatus::from((string) ($item['status'] ?? 'unknown')),
                durationMs: (float) ($item['durationMs'] ?? 0),
                fallbackUsed: (bool) ($item['fallbackUsed'] ?? false),
                requestedEngineId: is_string($item['requestedEngineId'] ?? null) ? $item['requestedEngineId'] : null,
                reason: is_string($item['reason'] ?? null) ? $item['reason'] : null,
                metadata: is_array($item['metadata'] ?? null) ? $item['metadata'] : [],
                startedAt: is_string($item['startedAt'] ?? null) ? $item['startedAt'] : null,
                completedAt: is_string($item['completedAt'] ?? null) ? $item['completedAt'] : null,
            );
        }

        return $executions;
    }

    public function saveExecution(RuntimeExecution $execution): void
    {
        $items = array_map(
            static fn (RuntimeExecution $item): array => $item->toArray(),
            $this->listExecutions(),
        );
        $items[] = $execution->toArray();
        $this->store->write(self::EXECUTIONS_FILE, $items);
    }

    public function findExecution(string $pipelineId): ?RuntimeExecution
    {
        foreach ($this->listExecutions() as $execution) {
            if ($execution->id === $pipelineId) {
                return $execution;
            }
        }

        return null;
    }

    public function saveValidationReport(string $pipelineId, array $report): void
    {
        $reports = $this->store->read(self::REPORTS_FILE);
        $map = is_array($reports) ? $reports : [];
        $map[$pipelineId] = $report;
        $this->store->write(self::REPORTS_FILE, $map);
    }

    public function findValidationReport(string $pipelineId): ?array
    {
        $reports = $this->store->read(self::REPORTS_FILE);

        if (!is_array($reports) || !isset($reports[$pipelineId]) || !is_array($reports[$pipelineId])) {
            return null;
        }

        return $reports[$pipelineId];
    }
}
