<?php

declare(strict_types=1);

namespace App\Application\ShadowKnowledge;

use App\Domain\ShadowKnowledge\KnowledgeGraph;

final class PrerequisiteChecker
{
    /** @return list<array{key: string, label: string, mastered: bool, reason: string}> */
    public function prerequisitesFor(KnowledgeGraph $graph, string $targetKey): array
    {
        $items = [];

        foreach ($graph->edges()->prerequisitesFor($targetKey) as $edge) {
            $mastery = $graph->masteries()->find($edge->fromKey());

            $items[] = [
                'key' => $edge->fromKey(),
                'label' => $graph->nodes()->find($edge->fromKey())?->label() ?? $edge->fromKey(),
                'mastered' => null !== $mastery && $mastery->mastered(),
                'reason' => $edge->reason(),
            ];
        }

        if ([] === $items) {
            foreach ($this->fallbackMap($targetKey) as $key => $reason) {
                $mastery = $graph->masteries()->find($key);

                $items[] = [
                    'key' => $key,
                    'label' => $graph->nodes()->find($key)?->label() ?? ucwords(str_replace('_', ' ', $key)),
                    'mastered' => null !== $mastery && $mastery->mastered(),
                    'reason' => $reason,
                ];
            }
        }

        return $items;
    }

    public function readinessPercent(KnowledgeGraph $graph, string $targetKey): int
    {
        $prerequisites = $this->prerequisitesFor($graph, $targetKey);

        if ([] === $prerequisites) {
            return 100;
        }

        $mastered = count(array_filter($prerequisites, static fn (array $item): bool => $item['mastered']));

        return (int) round(($mastered / count($prerequisites)) * 100);
    }

    /** @return array<string, string> */
    private function fallbackMap(string $targetKey): array
    {
        return match ($targetKey) {
            'cqrs' => [
                'repository_pattern' => 'CQRS separates reads and writes often via repositories.',
                'dependency_injection' => 'Handlers and buses rely on dependency injection.',
                'event_dispatcher' => 'CQRS flows frequently emit domain events.',
            ],
            'cuda' => [
                'gpu' => 'CUDA targets GPU hardware.',
                'parallelism' => 'CUDA workloads are massively parallel.',
                'threads' => 'Thread blocks organize CUDA kernels.',
            ],
            'kubernetes' => [
                'docker' => 'Kubernetes orchestrates containers built with Docker.',
            ],
            default => [],
        };
    }
}
