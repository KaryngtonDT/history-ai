<?php

declare(strict_types=1);

namespace App\Infrastructure\Agent;

use App\Domain\Agent\AgentPlan;
use App\Domain\Agent\AgentPlannerInterface;
use App\Domain\Agent\AgentRequest;
use App\Domain\Agent\AgentTool;

final class DeterministicAgentPlanner implements AgentPlannerInterface
{
    /** @var list<string> */
    private const array COMPARISON_KEYWORDS = [
        'compare',
        'versus',
        'vs',
        'difference',
        'comparez',
        'différence',
        'unterschied',
        'vergleichen',
    ];

    /** @var list<string> */
    private const array MEMORY_KEYWORDS = [
        'previous',
        'earlier',
        'history',
        'conversation',
        'précédent',
        'historique',
        'vorher',
        'verlauf',
    ];

    public function plan(AgentRequest $request): AgentPlan
    {
        $normalizedQuestion = mb_strtolower($request->question());

        $plan = AgentPlan::empty()
            ->append(
                AgentTool::SemanticSearch,
                'Retrieve relevant document chunks for the question',
            );

        if ($this->containsKeyword($normalizedQuestion, self::COMPARISON_KEYWORDS)) {
            $plan = $plan->append(
                AgentTool::KnowledgeGraph,
                'Explore artifact relationships relevant to the question',
            );
        }

        if ($this->containsKeyword($normalizedQuestion, self::MEMORY_KEYWORDS)) {
            $plan = $plan->append(
                AgentTool::ConversationMemory,
                'Include prior conversation context',
            );
        }

        return $plan->append(
            AgentTool::MultiDocumentChat,
            'Generate the final answer from gathered context',
        );
    }

    /**
     * @param list<string> $keywords
     */
    private function containsKeyword(string $normalizedQuestion, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($normalizedQuestion, mb_strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }
}
