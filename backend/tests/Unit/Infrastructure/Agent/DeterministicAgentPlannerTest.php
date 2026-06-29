<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Agent;

use App\Domain\Agent\AgentPlan;
use App\Domain\Agent\AgentPlannerInterface;
use App\Domain\Agent\AgentRequest;
use App\Domain\Agent\AgentStep;
use App\Domain\Agent\AgentTool;
use App\Infrastructure\Agent\DeterministicAgentPlanner;
use PHPUnit\Framework\TestCase;

final class DeterministicAgentPlannerTest extends TestCase
{
    private DeterministicAgentPlanner $planner;

    protected function setUp(): void
    {
        $this->planner = new DeterministicAgentPlanner();
    }

    public function testImplementsAgentPlannerInterface(): void
    {
        self::assertInstanceOf(AgentPlannerInterface::class, $this->planner);
    }

    public function testBuildsDefaultPlan(): void
    {
        $plan = $this->planner->plan(new AgentRequest('What is Rome?'));

        self::assertSame(
            [AgentTool::SemanticSearch, AgentTool::MultiDocumentChat],
            $this->toolSequence($plan),
        );
    }

    public function testBuildsComparisonPlan(): void
    {
        $plan = $this->planner->plan(new AgentRequest('Compare Rome versus Byzantium'));

        self::assertSame(
            [
                AgentTool::SemanticSearch,
                AgentTool::KnowledgeGraph,
                AgentTool::MultiDocumentChat,
            ],
            $this->toolSequence($plan),
        );
    }

    public function testBuildsMemoryPlan(): void
    {
        $plan = $this->planner->plan(new AgentRequest('What did we discuss earlier?'));

        self::assertSame(
            [
                AgentTool::SemanticSearch,
                AgentTool::ConversationMemory,
                AgentTool::MultiDocumentChat,
            ],
            $this->toolSequence($plan),
        );
    }

    public function testBuildsCombinedComparisonAndMemoryPlan(): void
    {
        $plan = $this->planner->plan(
            new AgentRequest('Compare our previous conversation about Rome versus Byzantium'),
        );

        self::assertSame(
            [
                AgentTool::SemanticSearch,
                AgentTool::KnowledgeGraph,
                AgentTool::ConversationMemory,
                AgentTool::MultiDocumentChat,
            ],
            $this->toolSequence($plan),
        );
    }

    public function testDetectsFrenchComparisonKeywords(): void
    {
        $plan = $this->planner->plan(
            new AgentRequest('Quelle est la différence entre Rome et Byzance ?'),
        );

        self::assertTrue($plan->containsTool(AgentTool::KnowledgeGraph));
    }

    public function testDetectsFrenchMemoryKeywords(): void
    {
        $plan = $this->planner->plan(
            new AgentRequest('Rappel historique précédent sur Rome'),
        );

        self::assertTrue($plan->containsTool(AgentTool::ConversationMemory));
    }

    public function testDetectsGermanComparisonKeywords(): void
    {
        $plan = $this->planner->plan(
            new AgentRequest('Vergleichen Sie den Unterschied zwischen Rom und Byzanz'),
        );

        self::assertTrue($plan->containsTool(AgentTool::KnowledgeGraph));
    }

    public function testDetectsGermanMemoryKeywords(): void
    {
        $plan = $this->planner->plan(
            new AgentRequest('Was haben wir vorher im Verlauf besprochen?'),
        );

        self::assertTrue($plan->containsTool(AgentTool::ConversationMemory));
    }

    public function testDetectsShortComparisonKeywordVs(): void
    {
        $plan = $this->planner->plan(new AgentRequest('Rome vs Byzantium'));

        self::assertTrue($plan->containsTool(AgentTool::KnowledgeGraph));
    }

    public function testNeverUsesDuplicateConsecutiveTools(): void
    {
        $questions = [
            'What is Rome?',
            'Compare Rome versus Byzantium',
            'What did we discuss earlier?',
            'Compare our previous conversation about Rome versus Byzantium',
            'Quelle est la différence entre Rome et Byzance ?',
            'Was haben wir vorher im Verlauf besprochen?',
        ];

        foreach ($questions as $question) {
            $this->assertNoDuplicateConsecutiveTools(
                $this->planner->plan(new AgentRequest($question)),
            );
        }
    }

    public function testProducesDeterministicOutput(): void
    {
        $request = new AgentRequest('Compare Rome versus Byzantium');

        $firstPlan = $this->planner->plan($request);
        $secondPlan = $this->planner->plan($request);

        self::assertSame(
            $this->toolSequence($firstPlan),
            $this->toolSequence($secondPlan),
        );
        self::assertSame(
            $this->descriptionSequence($firstPlan),
            $this->descriptionSequence($secondPlan),
        );
    }

    /**
     * @return list<AgentTool>
     */
    private function toolSequence(AgentPlan $plan): array
    {
        return array_map(
            static fn (AgentStep $step): AgentTool => $step->tool(),
            $plan->steps()->all(),
        );
    }

    /**
     * @return list<string>
     */
    private function descriptionSequence(AgentPlan $plan): array
    {
        return array_map(
            static fn (AgentStep $step): string => $step->description(),
            $plan->steps()->all(),
        );
    }

    private function assertNoDuplicateConsecutiveTools(AgentPlan $plan): void
    {
        $steps = $plan->steps()->all();

        for ($index = 1; $index < count($steps); ++$index) {
            self::assertNotSame(
                $steps[$index - 1]->tool(),
                $steps[$index]->tool(),
                sprintf(
                    'Consecutive duplicate tool "%s" found in plan.',
                    $steps[$index]->tool()->value,
                ),
            );
        }
    }
}
