<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Benchmark;

use App\Domain\Engine\EngineRepositoryInterface;
use App\Infrastructure\Runtime\Discovery\BinaryScanner;
use App\Infrastructure\Storage\JsonFileStore;

final class BenchmarkRunner
{
    public function __construct(
        private readonly EngineRepositoryInterface $engineRepository,
        private readonly BinaryScanner $binaryScanner,
        private readonly JsonFileStore $store,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function runEngine(string $engineId): array
    {
        $engine = $this->engineRepository->findById($engineId);

        if (null === $engine) {
            return ['engineId' => $engineId, 'ok' => false, 'error' => 'Engine not found'];
        }

        $started = microtime(true);
        $ok = $engine->installed;

        if (null !== $engine->binaryName) {
            $ok = null !== $this->binaryScanner->locate($engine->binaryName);
        }

        $durationMs = round((microtime(true) - $started) * 1000, 2);
        $result = [
            'engineId' => $engineId,
            'capability' => $engine->capability->value,
            'ok' => $ok,
            'durationMs' => $durationMs,
            'qualityScore' => $ok ? 85.0 : 0.0,
            'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];

        $this->appendHistory($result);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function runFull(): array
    {
        $results = [];

        foreach ($this->engineRepository->all() as $engine) {
            $results[] = $this->runEngine($engine->id);
        }

        return [
            'ok' => [] === array_filter($results, static fn (array $r): bool => !($r['ok'] ?? false)),
            'results' => $results,
            'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function history(): array
    {
        $data = $this->store->read('benchmark-history.json');

        if (!isset($data['items']) || !is_array($data['items'])) {
            return [];
        }

        return array_values(array_filter($data['items'], is_array(...)));
    }

    /**
     * @param array<string, mixed> $result
     */
    private function appendHistory(array $result): void
    {
        $items = $this->history();
        $items[] = $result;

        if (count($items) > 200) {
            $items = array_slice($items, -200);
        }

        $this->store->write('benchmark-history.json', ['items' => $items]);
    }
}
