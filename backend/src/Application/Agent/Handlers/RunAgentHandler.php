<?php

declare(strict_types=1);

namespace App\Application\Agent\Handlers;

use App\Application\Agent\Commands\RunAgentCommand;
use App\Application\Agent\DTO\AgentExecutionResultDto;
use App\Domain\Agent\AgentMetadataCollection;
use App\Domain\Agent\AgentExecutionResult;
use App\Domain\Agent\AgentExecutionStatus;
use App\Domain\Agent\AgentExecutionStep;
use App\Domain\Agent\AgentExecutionStepCollection;
use App\Domain\Agent\AgentPlan;
use App\Domain\Agent\AgentPlannerInterface;
use App\Domain\Agent\AgentRequest;
use App\Domain\Agent\AgentStep;
use App\Domain\Agent\AgentToolExecution;
use App\Domain\Agent\AgentToolExecutorInterface;
use App\Domain\Chat\ConversationId;
use App\Domain\Content\ContentId;
use Throwable;

final class RunAgentHandler
{
    private const string FINAL_SUMMARY = 'Agent workflow completed.';

    private const string FAILED_STEP_SUMMARY = 'Tool execution failed.';

    public function __construct(
        private readonly AgentPlannerInterface $planner,
        private readonly AgentToolExecutorInterface $toolExecutor,
    ) {
    }

    public function __invoke(RunAgentCommand $command): AgentExecutionResultDto
    {
        new ContentId($command->contentId);

        $conversationId = $this->normalizeConversationId($command->conversationId);

        if (null !== $conversationId) {
            new ConversationId($conversationId);
        }

        $request = new AgentRequest($command->question);
        $plan = $this->planner->plan($request);
        $result = $this->executePlan(
            $plan,
            $command->question,
            $command->contentId,
            $conversationId,
        );

        return AgentExecutionResultDto::fromDomain($result);
    }

    private function executePlan(
        AgentPlan $plan,
        string $question,
        string $contentId,
        ?string $conversationId,
    ): AgentExecutionResult {
        $executionSteps = AgentExecutionStepCollection::empty();

        foreach ($plan->steps()->all() as $planStep) {
            $executionSteps = $executionSteps->append(
                $this->executeStep($planStep, $question, $contentId, $conversationId),
            );
        }

        return new AgentExecutionResult(
            $plan,
            $executionSteps,
            self::FINAL_SUMMARY,
            AgentMetadataCollection::fromExecutionSteps($executionSteps)->merge(),
        );
    }

    private function executeStep(
        AgentStep $planStep,
        string $question,
        string $contentId,
        ?string $conversationId,
    ): AgentExecutionStep {
        $toolExecution = new AgentToolExecution(
            $planStep->tool(),
            $question,
            $contentId,
            $conversationId,
        );

        try {
            $toolResult = $this->toolExecutor->execute($toolExecution);

            return new AgentExecutionStep(
                $planStep->order(),
                $planStep->tool(),
                AgentExecutionStatus::Completed,
                $toolResult->summary(),
                $toolResult->metadata(),
            );
        } catch (Throwable) {
            return new AgentExecutionStep(
                $planStep->order(),
                $planStep->tool(),
                AgentExecutionStatus::Failed,
                self::FAILED_STEP_SUMMARY,
            );
        }
    }

    private function normalizeConversationId(?string $conversationId): ?string
    {
        if (null === $conversationId) {
            return null;
        }

        $trimmed = trim($conversationId);

        return '' === $trimmed ? null : $trimmed;
    }
}
