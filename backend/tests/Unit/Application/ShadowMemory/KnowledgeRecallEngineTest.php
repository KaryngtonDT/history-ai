<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowMemory;

use App\Application\ShadowMemory\KnowledgeConnectionBuilder;
use App\Application\ShadowMemory\KnowledgeRecallEngine;
use App\Application\ShadowMemory\KnowledgeSimilarityResolver;
use App\Domain\ShadowMemory\KnowledgeItem;
use App\Domain\ShadowMemory\MemoryCategory;
use App\Domain\ShadowMemory\MemoryTimeline;
use PHPUnit\Framework\TestCase;

final class KnowledgeRecallEngineTest extends TestCase
{
    public function testRecallReturnsEmptyWhenNoConceptsMatch(): void
    {
        $timeline = MemoryTimeline::create();
        $recall = $this->engine()->recall($timeline, 'What is the weather today?');

        self::assertSame([], $recall->promptLines());
    }

    public function testRecallBuildsPromptLinesForKnownConcept(): void
    {
        $timeline = MemoryTimeline::create()
            ->upsertKnowledge(KnowledgeItem::start(
                'dependency_injection',
                'Dependency Injection',
                MemoryCategory::Concept,
                'Core Symfony concept.',
            )->withExposure('video-1', 'session-1'));

        $recall = $this->engine()->recall($timeline, 'How does dependency injection work?');

        self::assertNotEmpty($recall->promptLines());
        self::assertStringContainsString('Dependency Injection', implode(' ', $recall->promptLines()));
    }

    public function testRecallMentionsPrerequisiteWhenPresent(): void
    {
        $timeline = MemoryTimeline::create()
            ->upsertKnowledge(KnowledgeItem::start(
                'dependency_injection',
                'Dependency Injection',
                MemoryCategory::Concept,
                'Prerequisite.',
            ))
            ->upsertKnowledge(KnowledgeItem::start(
                'symfony_messenger',
                'Symfony Messenger',
                MemoryCategory::Concept,
                'Advanced topic.',
            ));

        $recall = $this->engine()->recall($timeline, 'Explain Symfony Messenger queues.');

        self::assertStringContainsString(
            'Dependency Injection',
            implode(' ', $recall->promptLines()),
        );
    }

    private function engine(): KnowledgeRecallEngine
    {
        return new KnowledgeRecallEngine(
            new KnowledgeSimilarityResolver(),
            new KnowledgeConnectionBuilder(),
        );
    }
}
