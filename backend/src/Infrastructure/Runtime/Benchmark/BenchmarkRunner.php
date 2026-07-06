<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Benchmark;

use App\Infrastructure\Storage\JsonFileStore;

final class BenchmarkRunner
{
    public function __construct(
        private readonly EngineTestRunner $engineTestRunner,
        private readonly JsonFileStore $store,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function runEngine(string $engineId): array
    {
        $result = $this->engineTestRunner->run($engineId);
        $this->appendHistory($result);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function runFull(): array
    {
        $results = [];
        $engineIds = [
            'faster_whisper_large_v3', 'parakeet', 'canary',
            'ollama_gemma3', 'ollama_qwen3', 'ollama_deepseek_r1_distill',
            'f5_tts', 'kokoro', 'dia',
            'openvoice_v2', 'chatterbox', 'xtts_v2',
            'latentsync', 'echomimic_v2', 'wav2lip',
            'ffmpeg', 'ffmpeg_nvenc', 'ffmpeg_av1',
        ];

        foreach ($engineIds as $engineId) {
            $results[] = $this->runEngine($engineId);
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
