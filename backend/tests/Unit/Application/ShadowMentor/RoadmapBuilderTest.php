<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowMentor;

use App\Application\ShadowMentor\RoadmapBuilder;
use App\Domain\ShadowGoals\CareerGoal;
use App\Domain\ShadowMentor\RoadmapHorizon;
use PHPUnit\Framework\TestCase;

final class RoadmapBuilderTest extends TestCase
{
    public function testBuildsFiveHorizonsForBackendCareerGoal(): void
    {
        $goal = CareerGoal::create('Senior Backend Developer');
        $roadmap = (new RoadmapBuilder())->build($goal);

        self::assertCount(5, $roadmap->all());
        self::assertSame(RoadmapHorizon::Today, $roadmap->all()[0]->horizon());
        self::assertSame(RoadmapHorizon::Goal, $roadmap->all()[4]->horizon());
        self::assertSame('PHP', $roadmap->all()[0]->label());
    }

    public function testReturnsEmptyRoadmapWithoutGoal(): void
    {
        self::assertSame([], (new RoadmapBuilder())->build(null)->all());
    }
}
