<?php

declare(strict_types=1);

namespace App\Infrastructure\Agent;

use App\Application\Graph\Handlers\GetKnowledgeGraphHandler;
use App\Application\Graph\Queries\GetKnowledgeGraphQuery;
use App\Domain\Agent\AgentTool;
use App\Domain\Agent\AgentToolExecution;
use App\Domain\Agent\AgentToolExecutionResult;
use App\Domain\Agent\AgentToolExecutorInterface;

final class KnowledgeGraphToolExecutor implements AgentToolExecutorInterface
{
    public function __construct(
        private readonly GetKnowledgeGraphHandler $getKnowledgeGraphHandler,
    ) {
    }

    public function execute(AgentToolExecution $execution): AgentToolExecutionResult
    {
        $graphResult = ($this->getKnowledgeGraphHandler)(
            new GetKnowledgeGraphQuery($execution->contentId()),
        );

        $nodeCount = count($graphResult->nodes);
        $edgeCount = count($graphResult->edges);

        if (0 === $nodeCount) {
            return new AgentToolExecutionResult(
                tool: AgentTool::KnowledgeGraph,
                summary: 'Knowledge graph is empty.',
                metadata: [
                    'nodeCount' => 0,
                    'edgeCount' => 0,
                ],
            );
        }

        return new AgentToolExecutionResult(
            tool: AgentTool::KnowledgeGraph,
            summary: sprintf(
                'Knowledge graph contains %d nodes and %d relationships.',
                $nodeCount,
                $edgeCount,
            ),
            metadata: [
                'nodeCount' => $nodeCount,
                'edgeCount' => $edgeCount,
            ],
        );
    }
}
