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
}
