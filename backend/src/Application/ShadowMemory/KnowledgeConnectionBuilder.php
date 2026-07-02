<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory;

use App\Domain\ShadowMemory\KnowledgeConnection;
use App\Domain\ShadowMemory\KnowledgeConnectionCollection;
use App\Domain\ShadowMemory\MemoryTimeline;

final class KnowledgeConnectionBuilder
{
    public function build(MemoryTimeline $timeline): KnowledgeConnectionCollection
    {
        $connections = KnowledgeConnectionCollection::empty();
        $keys = array_map(
            static fn ($item) => $item->key(),
            $timeline->knowledge()->all(),
        );

        $preset = [
            ['docker', 'kubernetes', 'Containers to orchestration'],
            ['kubernetes', 'cloud', 'Orchestration to cloud deployment'],
            ['gpu', 'cuda', 'Hardware to parallel compute'],
            ['dependency_injection', 'symfony_messenger', 'DI foundation for Messenger'],
            ['dependency_injection', 'event_dispatcher', 'DI supports event dispatching'],
            ['repository_pattern', 'ddd', 'Repository pattern in DDD'],
            ['cqrs', 'symfony_messenger', 'CQRS often implemented with Messenger'],
            ['nietzsche', 'existentialism', 'Philosophy lineage'],
        ];

        foreach ($preset as [$from, $to, $label]) {
            if (in_array($from, $keys, true) && in_array($to, $keys, true)) {
                $connections = $connections->append(
                    KnowledgeConnection::link($from, $to, $label, 'Deterministic knowledge path from observed concepts.'),
                );
            }
        }

        return $connections;
    }
}
