<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory;

final class KnowledgeSimilarityResolver
{
    /** @var array<string, string> */
    private const CONCEPT_ALIASES = [
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
        'nietzsche' => 'nietzsche',
        'cqrs' => 'cqrs',
        'ddd' => 'ddd',
        'service locator' => 'service_locator',
    ];

    /**
     * @return list<array{key: string, label: string}>
     */
    public function extractConcepts(string $text): array
    {
        $lower = strtolower($text);
        $found = [];

        foreach (self::CONCEPT_ALIASES as $needle => $key) {
            if (str_contains($lower, $needle)) {
                $found[$key] = [
                    'key' => $key,
                    'label' => ucwords(str_replace('_', ' ', $key)),
                ];
            }
        }

        return array_values($found);
    }

    public function normalize(string $label): string
    {
        $lower = strtolower(trim($label));

        return self::CONCEPT_ALIASES[$lower] ?? str_replace(' ', '_', $lower);
    }
}
