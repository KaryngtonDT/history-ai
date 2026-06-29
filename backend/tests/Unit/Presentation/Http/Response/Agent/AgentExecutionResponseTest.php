<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Http\Response\Agent;

use App\Application\Agent\DTO\AgentExecutionResultDto;
use App\Application\Agent\DTO\AgentExecutionStepResult;
use App\Application\Agent\DTO\AgentPlanStepResult;
use App\Presentation\Http\Response\Agent\AgentExecutionResponse;
use PHPUnit\Framework\TestCase;

final class AgentExecutionResponseTest extends TestCase
{
    public function testExposesStepAndAggregatedMetadata(): void
    {
        $result = new AgentExecutionResultDto(
            plan: [
                new AgentPlanStepResult(0, 'semantic_search', 'Search'),
            ],
            steps: [
                new AgentExecutionStepResult(
                    0,
                    'semantic_search',
                    'completed',
                    'Semantic search found 2 relevant chunks.',
                    ['resultCount' => 2, 'topScore' => 0.91],
                ),
            ],
            finalSummary: 'Agent workflow completed.',
            metadata: ['resultCount' => 2, 'topScore' => 0.91],
        );

        $response = AgentExecutionResponse::fromResult($result);

        self::assertSame(
            ['resultCount' => 2, 'topScore' => 0.91],
            $response['metadata'],
        );
        self::assertSame(
            ['resultCount' => 2, 'topScore' => 0.91],
            $response['steps'][0]['metadata'],
        );
        self::assertSame('Agent workflow completed.', $response['finalSummary']);
    }
}
