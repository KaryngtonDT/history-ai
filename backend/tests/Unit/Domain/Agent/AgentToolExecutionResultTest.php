<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\AgentTool;
use App\Domain\Agent\AgentToolExecutionResult;
use PHPUnit\Framework\TestCase;

final class AgentToolExecutionResultTest extends TestCase
{
    public function testExposesToolSummaryAndMetadata(): void
    {
        $result = new AgentToolExecutionResult(
            AgentTool::SemanticSearch,
            'Semantic search prepared.',
            ['resultCount' => 3],
        );

        self::assertSame(AgentTool::SemanticSearch, $result->tool());
        self::assertSame('Semantic search prepared.', $result->summary());
        self::assertSame(['resultCount' => 3], $result->metadata());
    }

    public function testEmptyFactoryReturnsEmptySummaryAndMetadata(): void
    {
        $result = AgentToolExecutionResult::empty();

        self::assertSame(AgentTool::SemanticSearch, $result->tool());
        self::assertSame('', $result->summary());
        self::assertSame([], $result->metadata());
    }

    public function testIsImmutable(): void
    {
        $result = new AgentToolExecutionResult(
            AgentTool::KnowledgeGraph,
            'Knowledge graph exploration prepared.',
            ['nodeCount' => 2],
        );

        self::assertSame(AgentTool::KnowledgeGraph, $result->tool());
        self::assertSame(['nodeCount' => 2], $result->metadata());
    }

    public function testEqualsComparesToolSummaryAndMetadata(): void
    {
        $first = new AgentToolExecutionResult(
            AgentTool::MultiDocumentChat,
            'Multi-document chat prepared.',
            ['answerLength' => 42],
        );
        $second = new AgentToolExecutionResult(
            AgentTool::MultiDocumentChat,
            'Multi-document chat prepared.',
            ['answerLength' => 42],
        );
        $differentMetadata = new AgentToolExecutionResult(
            AgentTool::MultiDocumentChat,
            'Multi-document chat prepared.',
            ['answerLength' => 99],
        );

        self::assertTrue($first->equals($second));
        self::assertFalse($first->equals($differentMetadata));
    }
}
