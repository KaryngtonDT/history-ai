<?php

declare(strict_types=1);

namespace App\Application\ShadowKnowledge;

use App\Domain\ShadowKnowledge\KnowledgeConfidence;
use App\Domain\ShadowKnowledge\KnowledgeEdge;
use App\Domain\ShadowKnowledge\KnowledgeEdgeCollection;
use App\Domain\ShadowKnowledge\KnowledgeEdgeType;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowKnowledge\KnowledgeNodeType;

final class KnowledgeEdgeResolver
{
    /** @var list<array{string, string, KnowledgeEdgeType, string, string}> */
    private const PRESET = [
        ['dependency_injection', 'repository_pattern', KnowledgeEdgeType::Prerequisite, 'DI → Repository', 'Repositories are typically injected via the container.'],
        ['repository_pattern', 'doctrine', KnowledgeEdgeType::Prerequisite, 'Repository → Doctrine', 'Doctrine provides repository implementations.'],
        ['doctrine', 'entity_manager', KnowledgeEdgeType::Introduces, 'Doctrine → EntityManager', 'Doctrine centers on the entity manager.'],
        ['entity_manager', 'transactions', KnowledgeEdgeType::DependsOn, 'EntityManager → Transactions', 'Persistence changes rely on transaction boundaries.'],
        ['dependency_injection', 'event_dispatcher', KnowledgeEdgeType::RelatedTo, 'DI → Event Dispatcher', 'Both are core Symfony extension points.'],
        ['dependency_injection', 'symfony_messenger', KnowledgeEdgeType::Prerequisite, 'DI → Messenger', 'Messenger handlers are wired through DI.'],
        ['cqrs', 'symfony_messenger', KnowledgeEdgeType::UsedBy, 'CQRS → Messenger', 'CQRS flows are often implemented with Messenger.'],
        ['cqrs', 'event_sourcing', KnowledgeEdgeType::RelatedTo, 'CQRS → Event Sourcing', 'CQRS pairs naturally with event sourcing.'],
        ['event_sourcing', 'event_dispatcher', KnowledgeEdgeType::Prerequisite, 'Event Sourcing → Event Dispatcher', 'Events must be dispatched and handled explicitly.'],
        ['docker', 'kubernetes', KnowledgeEdgeType::Prerequisite, 'Docker → Kubernetes', 'Kubernetes orchestrates Docker containers.'],
        ['kubernetes', 'helm', KnowledgeEdgeType::Extends, 'Kubernetes → Helm', 'Helm packages Kubernetes deployments.'],
        ['gpu', 'cuda', KnowledgeEdgeType::Prerequisite, 'GPU → CUDA', 'CUDA programs run on GPU hardware.'],
        ['gpu', 'parallelism', KnowledgeEdgeType::Prerequisite, 'GPU → Parallelism', 'GPUs excel at parallel workloads.'],
        ['parallelism', 'threads', KnowledgeEdgeType::Introduces, 'Parallelism → Threads', 'Threads are a common parallel unit.'],
        ['threads', 'kernel', KnowledgeEdgeType::DependsOn, 'Threads → Kernel', 'Thread scheduling is managed by the OS kernel.'],
        ['kernel', 'simd', KnowledgeEdgeType::Extends, 'Kernel → SIMD', 'SIMD extends parallel execution within cores.'],
    ];

    public function resolve(KnowledgeGraph $graph): KnowledgeEdgeCollection
    {
        $edges = KnowledgeEdgeCollection::empty();
        $keys = array_map(static fn ($node) => $node->key(), $graph->nodes()->all());

        foreach (self::PRESET as [$from, $to, $type, $label, $reason]) {
            if (in_array($from, $keys, true) && in_array($to, $keys, true)) {
                $edges = $edges->append(KnowledgeEdge::link(
                    $from,
                    $to,
                    $type,
                    $label,
                    $reason,
                    'preset',
                    KnowledgeConfidence::High,
                ));
            }
        }

        return $edges;
    }

    public function nodeTypeForKey(string $key): KnowledgeNodeType
    {
        return match ($key) {
            'docker', 'kubernetes', 'helm', 'cuda', 'gpu' => KnowledgeNodeType::Technology,
            'symfony_messenger', 'doctrine' => KnowledgeNodeType::Framework,
            default => KnowledgeNodeType::Concept,
        };
    }
}
