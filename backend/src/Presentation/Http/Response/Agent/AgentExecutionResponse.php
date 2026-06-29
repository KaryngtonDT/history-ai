<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Agent;

use App\Application\Agent\DTO\AgentExecutionResultDto;

final class AgentExecutionResponse
{
    /**
     * @return array{
     *     plan: list<array{order: int, tool: string, description: string}>,
     *     steps: list<array{order: int, tool: string, status: string, summary: string}>,
     *     finalSummary: string
     * }
     */
    public static function fromResult(AgentExecutionResultDto $result): array
    {
        return [
            'plan' => array_map(
                static fn ($step): array => [
                    'order' => $step->order,
                    'tool' => $step->tool,
                    'description' => $step->description,
                ],
                $result->plan,
            ),
            'steps' => array_map(
                static fn ($step): array => [
                    'order' => $step->order,
                    'tool' => $step->tool,
                    'status' => $step->status,
                    'summary' => $step->summary,
                ],
                $result->steps,
            ),
            'finalSummary' => $result->finalSummary,
        ];
    }
}
