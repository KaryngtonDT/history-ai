<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\AgentExecutionStatus;
use App\Domain\Agent\AgentExecutionStep;
use App\Domain\Agent\AgentExecutionStepCollection;
use App\Domain\Agent\AgentMetadata;
use App\Domain\Agent\AgentMetadataCollection;
use App\Domain\Agent\AgentTool;
use PHPUnit\Framework\TestCase;

final class AgentMetadataCollectionTest extends TestCase
{
    public function testMergeCombinesMetadataFromAllItems(): void
    {
        $collection = new AgentMetadataCollection([
            new AgentMetadata(['resultCount' => 3, 'topScore' => 0.91]),
            new AgentMetadata(['nodeCount' => 12, 'edgeCount' => 18]),
            new AgentMetadata(['messageCount' => 4, 'sourceCount' => 2, 'citationCount' => 1]),
        ]);

        self::assertSame(
            [
                'resultCount' => 3,
                'topScore' => 0.91,
                'nodeCount' => 12,
                'edgeCount' => 18,
                'messageCount' => 4,
                'sourceCount' => 2,
                'citationCount' => 1,
            ],
            $collection->merge()->values(),
        );
    }

    public function testMergeOverwritesDuplicateKeysWithLaterValues(): void
    {
        $collection = new AgentMetadataCollection([
            new AgentMetadata(['messageCount' => 2, 'sourceCount' => 1]),
            new AgentMetadata(['messageCount' => 5, 'citationCount' => 3]),
        ]);

        self::assertSame(
            [
                'messageCount' => 5,
                'sourceCount' => 1,
                'citationCount' => 3,
            ],
            $collection->merge()->values(),
        );
    }

    public function testMergeReturnsEmptyMetadataWhenCollectionIsEmpty(): void
    {
        self::assertSame([], AgentMetadataCollection::empty()->merge()->values());
    }

    public function testFromExecutionStepsAggregatesStepMetadataInOrder(): void
    {
        $steps = AgentExecutionStepCollection::empty()
            ->append(new AgentExecutionStep(
                0,
                AgentTool::SemanticSearch,
                AgentExecutionStatus::Completed,
                'Semantic search found 2 relevant chunks.',
                ['resultCount' => 2, 'topScore' => 0.91],
            ))
            ->append(new AgentExecutionStep(
                1,
                AgentTool::MultiDocumentChat,
                AgentExecutionStatus::Completed,
                'Multi-document chat requires a conversation.',
                ['requiresConversation' => true],
            ));

        self::assertSame(
            [
                'resultCount' => 2,
                'topScore' => 0.91,
                'requiresConversation' => true,
            ],
            AgentMetadataCollection::fromExecutionSteps($steps)->merge()->values(),
        );
    }

    public function testAppendAddsMetadataItem(): void
    {
        $collection = AgentMetadataCollection::empty()
            ->append(new AgentMetadata(['resultCount' => 1]))
            ->append(new AgentMetadata(['nodeCount' => 3]));

        self::assertSame(2, $collection->count());
        self::assertSame(
            ['resultCount' => 1, 'nodeCount' => 3],
            $collection->merge()->values(),
        );
    }
}
