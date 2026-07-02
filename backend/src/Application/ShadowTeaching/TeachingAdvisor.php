<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching;

use App\Domain\ShadowTeaching\LearningObjective;
use App\Domain\ShadowTeaching\TeachingProgressStatus;
use App\Domain\ShadowTeaching\TeachingRecommendation;

final class TeachingAdvisor
{
    public function recommend(?LearningObjective $current, ?LearningObjective $next): TeachingRecommendation
    {
        if (null === $current) {
            if (null !== $next) {
                return new TeachingRecommendation(
                    sprintf('Start with %s.', $next->title()),
                    'start_lesson',
                    $next->key(),
                );
            }

            return TeachingRecommendation::empty();
        }

        if (TeachingProgressStatus::Mastered === $current->status() && null !== $next) {
            return new TeachingRecommendation(
                sprintf('You already master %s. Move to %s.', $current->title(), $next->title()),
                'advance',
                $next->key(),
            );
        }

        if (TeachingProgressStatus::ReviewNeeded === $current->status()) {
            return new TeachingRecommendation(
                sprintf('%s deserves a revision before continuing.', $current->title()),
                'review',
                $current->key(),
            );
        }

        return new TeachingRecommendation(
            sprintf('Today we work on %s.', $current->title()),
            'continue',
            $current->key(),
        );
    }
}
