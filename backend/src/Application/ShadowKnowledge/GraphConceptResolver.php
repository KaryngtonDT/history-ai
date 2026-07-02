<?php

declare(strict_types=1);

namespace App\Application\ShadowKnowledge;

final class GraphConceptResolver
{
    /** @var array<string, string> */
    private const ALIASES = [
        'dependency injection' => 'dependency_injection',
        'di' => 'dependency_injection',
        'repository pattern' => 'repository_pattern',
        'repository' => 'repository_pattern',
        'event dispatcher' => 'event_dispatcher',
        'symfony messenger' => 'symfony_messenger',
        'messenger' => 'symfony_messenger',
        'docker' => 'docker',
        'kubernetes' => 'kubernetes',
        'k8s' => 'kubernetes',
        'gpu' => 'gpu',
        'cuda' => 'cuda',
        'parallelism' => 'parallelism',
        'threads' => 'threads',
        'kernel' => 'kernel',
        'simd' => 'simd',
        'cqrs' => 'cqrs',
        'event sourcing' => 'event_sourcing',
        'ddd' => 'ddd',
        'doctrine' => 'doctrine',
        'entity manager' => 'entity_manager',
        'transactions' => 'transactions',
    ];

    /** @return list<array{key: string, label: string}> */
    public function extractConcepts(string $text): array
    {
        $lower = strtolower($text);
        $found = [];

        foreach (self::ALIASES as $needle => $key) {
            if (str_contains($lower, $needle)) {
                $found[$key] = [
                    'key' => $key,
                    'label' => ucwords(str_replace('_', ' ', $key)),
                ];
            }
        }

        return array_values($found);
    }
}
