<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowExecutive;

use App\Application\ShadowExecutive\PriorityResolver;
use App\Domain\ShadowExecutive\ExecutivePriority;
use App\Domain\ShadowGoals\CareerGoal;
use App\Domain\ShadowGoals\GoalPriority;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use PHPUnit\Framework\TestCase;

final class PriorityResolverTest extends TestCase
{
    public function testPrimaryGoalWithNearDeadlineIsCritical(): void
    {
        $goal = CareerGoal::create('Kubernetes')
            ->applyUpdate([
                'priority' => GoalPriority::Primary->value,
                'deadline' => (new \DateTimeImmutable('+7 days'))->format(DATE_ATOM),
            ]);

        $priority = (new PriorityResolver())->resolve($goal, KnowledgeGraph::create());

        self::assertSame(ExecutivePriority::Critical, $priority);
    }

    public function testLowMasteryConceptIsHighPriority(): void
    {
        $priority = (new PriorityResolver())->resolve(
            null,
            KnowledgeGraph::create(),
            'docker',
            20,
        );

        self::assertSame(ExecutivePriority::High, $priority);
    }
}
