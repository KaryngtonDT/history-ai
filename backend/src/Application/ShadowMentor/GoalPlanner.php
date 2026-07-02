<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor;

use App\Domain\ShadowGoals\GoalMilestone;
use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowGoals\LearningGoal;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowMentor\GoalMilestoneCollection;
use App\Domain\ShadowMentor\MentorPlan;
use App\Domain\ShadowMentor\SkillProgress;
use App\Domain\ShadowMentor\SkillProgressCollection;
use App\Domain\ShadowMentor\WeeklyReview;

final class GoalPlanner
{
    public function __construct(
        private readonly RoadmapBuilder $roadmapBuilder,
        private readonly LearningMissionBuilder $missionBuilder,
        private readonly GoalProgressCalculator $progressCalculator,
        private readonly MilestoneResolver $milestoneResolver,
        private readonly MultiGoalOrchestrator $orchestrator,
    ) {
    }

    public function plan(
        GoalPortfolio $portfolio,
        MentorPlan $plan,
        KnowledgeGraph $graph,
    ): MentorPlan {
        $primary = $portfolio->primaryGoal();

        if (null === $primary || !$portfolio->mentorEnabled()) {
            return $plan;
        }

        $missions = $this->orchestrator->missionsForGoals($portfolio, $graph, $this->missionBuilder);
        $current = $missions->current();
        $skills = $this->skillsForGoals($portfolio, $graph);
        $milestones = $this->milestoneResolver->resolve($primary);
        $estimated = (new \DateTimeImmutable())->modify('+18 months');

        return $plan
            ->withMissions($missions)
            ->withRoadmap($this->roadmapBuilder->build($primary))
            ->withSkills($skills)
            ->withMilestones($milestones)
            ->withCurrentMissionId($current?->id())
            ->withEstimatedCompletionAt($estimated)
            ->withWeeklyReview($this->weeklyReview($primary, $plan));
    }

    private function skillsForGoals(GoalPortfolio $portfolio, KnowledgeGraph $graph): SkillProgressCollection
    {
        $collection = SkillProgressCollection::empty();

        foreach ($portfolio->goals()->all() as $goal) {
            foreach ($goal->targetSkills() as $skillKey) {
                $mastery = $graph->masteries()->find($skillKey);
                $collection = $collection->upsert(SkillProgress::create(
                    $skillKey,
                    ucwords(str_replace('_', ' ', $skillKey)),
                    $mastery?->percent() ?? $goal->progressPercent(),
                ));
            }
        }

        return $collection;
    }

    private function weeklyReview(LearningGoal $primary, MentorPlan $plan): WeeklyReview
    {
        if ('' !== $plan->weeklyReview()->summary()) {
            return $plan->weeklyReview();
        }

        return WeeklyReview::generate(
            sprintf('Working toward %s.', $primary->title()),
            max(1, (int) round($primary->progressPercent() / 10)),
            0,
            'Keep a steady weekly rhythm.',
            ['Review current mission before starting new videos.'],
        );
    }
}
