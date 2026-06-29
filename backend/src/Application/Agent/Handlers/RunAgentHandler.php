<?php

declare(strict_types=1);

namespace App\Application\Agent\Handlers;

use App\Application\Agent\Commands\RunAgentCommand;
use App\Application\Agent\DTO\AgentExecutionResultDto;
use App\Domain\Agent\AgentExecutionResult;
use App\Domain\Agent\AgentExecutionStatus;
use App\Domain\Agent\AgentExecutionStep;
use App\Domain\Agent\AgentExecutionStepCollection;
use App\Domain\Agent\AgentPlan;
use App\Domain\Agent\AgentPlannerInterface;
use App\Domain\Agent\AgentRequest;
use App\Domain\Agent\AgentTool;
use App\Domain\Chat\ConversationId;
use App\Domain\Content\ContentId;

final class RunAgentHandler
{
    private const string FINAL_SUMMARY = 'Agent workflow completed.';

    public function __construct(
        private readonly AgentPlannerInterface $planner,
    ) {
    }

    public function __invoke(RunAgentCommand $command): AgentExecutionResultDto
    {
        new ContentId($command->contentId);

        if (null !== $command->conversationId && '' !== trim($command->conversationId)) {
            new ConversationId($command->conversationId);
        }

        $request = new AgentRequest($command->question);
        $plan = $this->planner->plan($request);
        $result = $this->executePlan($plan);

        return AgentExecutionResultDto::fromDomain($result);
    }

    private function executePlan(AgentPlan $plan): AgentExecutionResult
    {
        $executionSteps = AgentExecutionStepCollection::empty();

        foreach ($plan->steps()->all() as $planStep) {
            $executionSteps = $executionSteps->append(
                new AgentExecutionStep(
                    $planStep->order(),
                    $planStep->tool(),
                    AgentExecutionStatus::Completed,
                    $this->summaryForTool($planStep->tool()),
                ),
            );
        }

        return new AgentExecutionResult(
            $plan,
            $executionSteps,
            self::FINAL_SUMMARY,
        );
    }

    private function summaryForTool(AgentTool $tool): string
    {
        return match ($tool) {
            AgentTool::SemanticSearch => 'Semantic search prepared.',
            AgentTool::KnowledgeGraph => 'Knowledge graph exploration prepared.',
            AgentTool::ConversationMemory => 'Conversation memory prepared.',
            AgentTool::MultiDocumentChat => 'Multi-document chat prepared.',
        };
    }
}
