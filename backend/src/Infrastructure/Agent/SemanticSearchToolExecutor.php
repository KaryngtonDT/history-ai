<?php

declare(strict_types=1);

namespace App\Infrastructure\Agent;

use App\Application\Semantic\Handlers\SearchSemanticChunksHandler;
use App\Application\Semantic\Queries\SearchSemanticChunksQuery;
use App\Domain\Agent\AgentTool;
use App\Domain\Agent\AgentToolExecution;
use App\Domain\Agent\AgentToolExecutionResult;
use App\Domain\Agent\AgentToolExecutorInterface;

final class SemanticSearchToolExecutor implements AgentToolExecutorInterface
{
    public function __construct(
        private readonly SearchSemanticChunksHandler $searchSemanticChunksHandler,
    ) {
    }

    public function execute(AgentToolExecution $execution): AgentToolExecutionResult
    {
        $searchResult = ($this->searchSemanticChunksHandler)(
            new SearchSemanticChunksQuery(
                $execution->contentId(),
                $execution->question(),
            ),
        );

        $resultCount = count($searchResult->results);

        if (0 === $resultCount) {
            return new AgentToolExecutionResult(
                tool: AgentTool::SemanticSearch,
                summary: 'Semantic search found no relevant chunks.',
                metadata: ['resultCount' => 0],
            );
        }

        $topScore = max(array_map(
            static fn ($result): float => $result->score,
            $searchResult->results,
        ));

        return new AgentToolExecutionResult(
            tool: AgentTool::SemanticSearch,
            summary: sprintf('Semantic search found %d relevant chunks.', $resultCount),
            metadata: [
                'resultCount' => $resultCount,
                'topScore' => $topScore,
            ],
        );
    }
}
