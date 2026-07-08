<?php

declare(strict_types=1);

namespace App\Application\Runtime;

interface RuntimePlatformInterface
{
    /**
     * @return array<string, mixed>
     */
    public function overview(): array;

    /**
     * @return array<string, mixed>
     */
    public function readiness(): array;

    /**
     * @return array<string, mixed>
     */
    public function health(): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function engines(): array;

    /**
     * @return array<string, mixed>
     */
    public function catalog(): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function recommendations(): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function profiles(): array;

    /**
     * @return array<string, mixed>
     */
    public function testEngine(string $engineId): array;

    /**
     * @return array<string, mixed>
     */
    public function benchmark(?string $engineId = null): array;

    /**
     * @return array<string, mixed>
     */
    public function validatePipeline(): array;

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function updateProfile(array $payload): array;

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function updateSelection(array $payload): array;

    /**
     * @return array<string, mixed>|null
     */
    public function report(string $pipelineId): ?array;

    /**
     * @return array<string, mixed>
     */
    public function provisionEngine(string $engineId): array;

    /**
     * @return array<string, mixed>
     */
    public function provisionAll(): array;

    /**
     * @return array<string, mixed>
     */
    public function provisionCompatibleAll(): array;

    /**
     * @return array<string, mixed>
     */
    public function provisioningPlan(): array;

    /**
     * @return array<string, mixed>
     */
    public function hardware(): array;

    /**
     * @return array<string, mixed>
     */
    public function hardwareProfile(): array;

    /**
     * @return array<string, mixed>
     */
    public function compatibility(): array;

    /**
     * @return array<string, mixed>|null
     */
    public function engineCompatibility(string $engineId): ?array;

    /**
     * @return array<string, mixed>|null
     */
    public function engineBlockedReason(string $engineId): ?array;

    /**
     * @return array<string, mixed>
     */
    public function capabilityMaturity(): array;

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function resolve(array $payload): array;

    /**
     * @return array<string, mixed>
     */
    public function selection(): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function capabilities(): array;

    /**
     * @return array<string, mixed>
     */
    public function capabilitySelectionView(string $capability): array;

    /**
     * @return array<string, mixed>
     */
    public function engineManagement(): array;

    /**
     * @return array<string, mixed>
     */
    public function installEngine(string $engineId): array;

    /**
     * @return array<string, mixed>
     */
    public function updateEngine(string $engineId): array;

    /**
     * @return array<string, mixed>
     */
    public function repairEngine(string $engineId): array;

    /**
     * @return array<string, mixed>
     */
    public function removeEngine(string $engineId): array;

    /**
     * @return array<string, mixed>
     */
    public function validateEngine(string $engineId): array;

    /**
     * @return array<string, mixed>
     */
    public function benchmarkEngine(string $engineId): array;

    /**
     * @return array<string, mixed>|null
     */
    public function engineMetadata(string $engineId): ?array;

    /**
     * @return array<string, mixed>
     */
    public function recommendationProfiles(): array;

    /**
     * @return array<string, mixed>
     */
    public function doctorReport(): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function notifications(?int $limit = 20): array;
}
