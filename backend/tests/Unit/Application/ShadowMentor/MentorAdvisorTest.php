<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowMentor;

use App\Application\ShadowMentor\MentorAdvisor;
use App\Domain\ShadowGoals\CareerGoal;
use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowMentor\MentorMission;
use App\Domain\ShadowMentor\MentorMissionCollection;
use App\Domain\ShadowMentor\MentorPlan;
use App\Domain\ShadowMentor\WeeklyReview;
use PHPUnit\Framework\TestCase;

final class MentorAdvisorTest extends TestCase
{
    public function testRecommendIncludesCurrentMissionContext(): void
    {
        $portfolio = GoalPortfolio::create()->addGoal(CareerGoal::create('Senior Backend Developer'));
        $mission = MentorMission::create(
            $portfolio->primaryGoal()->id(),
            'Docker fundamentals',
            'Learn container basics',
            30,
            'docker',
        )->activate();
        $plan = MentorPlan::create()
            ->withMissions(MentorMissionCollection::empty()->append($mission))
            ->withCurrentMissionId($mission->id());

        $lines = (new MentorAdvisor())->recommend($portfolio, $plan, 'Explain this segment.');

        self::assertNotEmpty($lines);
        self::assertStringContainsString('Docker fundamentals', implode(' ', $lines));
    }

    public function testRecommendMentionsAdaptationWhenWeeklyReviewPending(): void
    {
        $portfolio = GoalPortfolio::create()->addGoal(CareerGoal::create('Senior Backend Developer'));
        $plan = MentorPlan::create()->withWeeklyReview(WeeklyReview::generate(
            'Weekly summary',
            2,
            0,
            'Steady pace',
            ['Adjust mission order'],
        ));

        $lines = (new MentorAdvisor())->recommend($portfolio, $plan);

        self::assertStringContainsString('adapt', strtolower(implode(' ', $lines)));
    }
}
