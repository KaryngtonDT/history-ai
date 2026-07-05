<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Benchmark;

use App\Domain\Engine\Engine;
use App\Domain\Engine\EngineRepositoryInterface;
use App\Domain\Runtime\EngineExecutionMode;
use App\Domain\Runtime\RuntimeStatus;
use App\Infrastructure\Runtime\Catalog\EngineCatalogDefinitions;

final class EngineTestRunner
{
    public function __construct(private readonly EngineRepositoryInterface $engineRepository)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function run(string $engineId): array
    {
        $engine = $this->engineRepository->findById($engineId);

        if (null === $engine) {
            return [
                'engineId' => $engineId,
                'status' => RuntimeStatus::Missing->value,
                'ok' => false,
                'mode' => EngineExecutionMode::Real->value,
                'fallbackUsed' => false,
                'error' => 'Engine not found in registry.',
            ];
        }

        $started = microtime(true);
        $probe = $this->probe($engine);
        $durationMs = round((microtime(true) - $started) * 1000, 2);
        $ok = RuntimeStatus::Ready === $engine->runtimeStatus && ($probe['ok'] ?? false);

        return [
            'engineId' => $engineId,
            'capability' => $engine->capability->value,
            'status' => $engine->runtimeStatus->value,
            'ok' => $ok,
            'mode' => $engine->executionMode->value,
            'durationMs' => $durationMs,
            'executableFound' => $engine->executableFound,
            'modelFound' => $engine->modelFound,
            'fallbackUsed' => false,
            'outputSample' => $probe['outputSample'] ?? null,
            'error' => $ok ? null : ($probe['error'] ?? $engine->errorReason),
            'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];
    }

    /**
     * @return array{ok: bool, outputSample?: string, error?: string}
     */
    private function probe(Engine $engine): array
    {
        if (RuntimeStatus::Mock === $engine->runtimeStatus) {
            return [
                'ok' => false,
                'error' => $engine->errorReason ?? 'Engine runs in shim/mock mode.',
                'outputSample' => 'mock',
            ];
        }

        if (RuntimeStatus::Missing === $engine->runtimeStatus) {
            return ['ok' => false, 'error' => $engine->errorReason ?? 'Executable missing.'];
        }

        if (null !== $engine->ollamaModelTag) {
            return [
                'ok' => $engine->executableFound && $engine->modelFound,
                'outputSample' => $engine->modelFound ? 'ollama model present' : null,
                'error' => $engine->errorReason,
            ];
        }

        if (null !== $engine->binaryName && null !== $engine->version?->build) {
            $binary = $engine->version->build;
            $output = shell_exec(escapeshellarg($binary).' -version 2>&1');

            if (!is_string($output) || '' === trim($output)) {
                $output = shell_exec(escapeshellarg($binary).' --help 2>&1');
            }

            if (!is_string($output) || '' === trim($output)) {
                return ['ok' => false, 'error' => 'Executable did not respond to version probe.'];
            }

            return [
                'ok' => $engine->isReady(),
                'outputSample' => strtok(trim($output), "\n") ?: trim($output),
                'error' => $engine->isReady() ? null : $engine->errorReason,
            ];
        }

        return [
            'ok' => $engine->isReady(),
            'error' => $engine->isReady() ? null : $engine->errorReason,
        ];
    }
}
