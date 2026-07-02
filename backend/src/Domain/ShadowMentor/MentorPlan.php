<?php

declare(strict_types=1);

namespace App\Domain\ShadowMentor;

use App\Domain\ShadowMentor\Exception\InvalidShadowMentorException;

final readonly class MentorPlan
{
    public function __construct(
        private MentorPlanId $id,
        private string $scopeKey,
        private MentorMissionCollection $missions,
        private RoadmapStepCollection $roadmap,
        private SkillProgressCollection $skills,
        private GoalMilestoneCollection $milestones,
        private ?string $currentMissionId,
        private ?\DateTimeImmutable $estimatedCompletionAt,
        private WeeklyReview $weeklyReview,
        private bool $mentorEnabled,
    ) {
        if ('' === trim($scopeKey)) {
            throw new InvalidShadowMentorException('Mentor plan scope cannot be empty.');
        }
    }

    public static function create(
        ?MentorPlanId $id = null,
        string $scopeKey = 'default',
    ): self {
        return new self(
            $id ?? MentorPlanId::generate(),
            trim($scopeKey),
            MentorMissionCollection::empty(),
            RoadmapStepCollection::empty(),
            SkillProgressCollection::empty(),
            GoalMilestoneCollection::empty(),
            null,
            null,
            WeeklyReview::empty(),
            true,
        );
    }

    public function id(): MentorPlanId
    {
        return $this->id;
    }

    public function scopeKey(): string
    {
        return $this->scopeKey;
    }

    public function missions(): MentorMissionCollection
    {
        return $this->missions;
    }

    public function roadmap(): RoadmapStepCollection
    {
        return $this->roadmap;
    }

    public function skills(): SkillProgressCollection
    {
        return $this->skills;
    }

    public function milestones(): GoalMilestoneCollection
    {
        return $this->milestones;
    }

    public function currentMissionId(): ?string
    {
        return $this->currentMissionId;
    }

    public function currentMission(): ?MentorMission
    {
        return null !== $this->currentMissionId
            ? $this->missions->find($this->currentMissionId)
            : $this->missions->current();
    }

    public function estimatedCompletionAt(): ?\DateTimeImmutable
    {
        return $this->estimatedCompletionAt;
    }

    public function weeklyReview(): WeeklyReview
    {
        return $this->weeklyReview;
    }

    public function mentorEnabled(): bool
    {
        return $this->mentorEnabled;
    }

    public function withMissions(MentorMissionCollection $missions): self
    {
        return $this->replace(missions: $missions);
    }

    public function withRoadmap(RoadmapStepCollection $roadmap): self
    {
        return $this->replace(roadmap: $roadmap);
    }

    public function withSkills(SkillProgressCollection $skills): self
    {
        return $this->replace(skills: $skills);
    }

    public function withMilestones(GoalMilestoneCollection $milestones): self
    {
        return $this->replace(milestones: $milestones);
    }

    public function withCurrentMissionId(?string $currentMissionId): self
    {
        return $this->replace(currentMissionId: $currentMissionId);
    }

    public function withEstimatedCompletionAt(?\DateTimeImmutable $estimatedCompletionAt): self
    {
        return $this->replace(estimatedCompletionAt: $estimatedCompletionAt);
    }

    public function withWeeklyReview(WeeklyReview $weeklyReview): self
    {
        return $this->replace(weeklyReview: $weeklyReview);
    }

    public function completeMission(string $missionId): self
    {
        $mission = $this->missions->find($missionId);

        if (null === $mission) {
            throw new InvalidShadowMentorException('Mission not found.');
        }

        $missions = $this->missions->upsert($mission->complete());

        return $this->replace(missions: $missions, currentMissionId: null);
    }

    public function reset(): self
    {
        return self::create($this->id, $this->scopeKey);
    }

    private function replace(
        ?MentorMissionCollection $missions = null,
        ?RoadmapStepCollection $roadmap = null,
        ?SkillProgressCollection $skills = null,
        ?GoalMilestoneCollection $milestones = null,
        ?string $currentMissionId = null,
        ?\DateTimeImmutable $estimatedCompletionAt = null,
        ?WeeklyReview $weeklyReview = null,
    ): self {
        return new self(
            $this->id,
            $this->scopeKey,
            $missions ?? $this->missions,
            $roadmap ?? $this->roadmap,
            $skills ?? $this->skills,
            $milestones ?? $this->milestones,
            $currentMissionId ?? $this->currentMissionId,
            $estimatedCompletionAt ?? $this->estimatedCompletionAt,
            $weeklyReview ?? $this->weeklyReview,
            $this->mentorEnabled,
        );
    }
}
