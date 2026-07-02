<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor\Handlers;

use App\Application\ShadowMentor\MentorBuilder;
use App\Application\ShadowMentor\MentorJsonMapper;

final class PostCompleteMissionHandler
{
    public function __construct(
        private readonly MentorBuilder $builder,
        private readonly MentorJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey, string $missionId): array
    {
        $plan = $this->builder->completeMission($scopeKey, $missionId);
        $mission = $plan->missions()->find($missionId);

        if (null === $mission) {
            return ['error' => 'Mission not found.'];
        }

        return $this->mapper->missionToArray($mission);
    }
}
