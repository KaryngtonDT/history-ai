<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

interface RuntimeRepositoryInterface
{
    public function getConfiguration(): RuntimeConfiguration;

    public function saveConfiguration(RuntimeConfiguration $configuration): void;

    /**
     * @return list<RuntimeExecution>
     */
    public function listExecutions(): array;

    public function saveExecution(RuntimeExecution $execution): void;

    public function findExecution(string $pipelineId): ?RuntimeExecution;

    /**
     * @param array<string, mixed> $report
     */
    public function saveValidationReport(string $pipelineId, array $report): void;

    /**
     * @return array<string, mixed>|null
     */
    public function findValidationReport(string $pipelineId): ?array;
}
