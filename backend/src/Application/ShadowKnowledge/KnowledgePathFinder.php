<?php

declare(strict_types=1);

namespace App\Application\ShadowKnowledge;

use App\Domain\ShadowKnowledge\KnowledgeGraph;

final class KnowledgePathFinder
{
    /** @return list<array{key: string, label: string}> */
    public function findPath(KnowledgeGraph $graph, string $fromKey, string $toKey): array
    {
        $visited = [];
        $path = $this->walk($graph, $fromKey, $toKey, $visited);

        return array_map(
            static fn (string $key) => [
                'key' => $key,
                'label' => $graph->nodes()->find($key)?->label() ?? $key,
            ],
            $path,
        );
    }

    /** @return list<array{key: string, label: string, steps: list<array{key: string, label: string}>}> */
    public function learningPaths(KnowledgeGraph $graph): array
    {
        $paths = [
            ['docker', 'kubernetes', 'helm'],
            ['dependency_injection', 'symfony_messenger', 'cqrs'],
            ['gpu', 'cuda'],
        ];
        $result = [];

        foreach ($paths as $keys) {
            $steps = [];

            foreach ($keys as $key) {
                if (null !== $graph->nodes()->find($key)) {
                    $steps[] = ['key' => $key, 'label' => $graph->nodes()->find($key)->label()];
                }
            }

            if (count($steps) >= 2) {
                $result[] = [
                    'key' => $steps[0]['key'].'_to_'.$steps[array_key_last($steps)]['key'],
                    'label' => $steps[0]['label'].' → '.$steps[array_key_last($steps)]['label'],
                    'steps' => $steps,
                ];
            }
        }

        return $result;
    }

    /** @param array<string, bool> $visited */
    private function walk(KnowledgeGraph $graph, string $current, string $target, array &$visited): array
    {
        if ($current === $target) {
            return [$current];
        }

        $visited[$current] = true;

        foreach ($graph->edges()->all() as $edge) {
            if ($edge->fromKey() !== $current || isset($visited[$edge->toKey()])) {
                continue;
            }

            $sub = $this->walk($graph, $edge->toKey(), $target, $visited);

            if ([] !== $sub) {
                return [$current, ...$sub];
            }
        }

        return [];
    }
}
