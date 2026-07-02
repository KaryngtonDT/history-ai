<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor;

use App\Domain\ShadowGoals\GoalPortfolio;

final class GoalAnalyzer
{
    /** @return array{goalCount: int, primaryTitle: ?string, secondaryCount: int} */
    public function analyze(GoalPortfolio $portfolio): array
    {
        return [
            'goalCount' => count($portfolio->goals()->all()),
            'primaryTitle' => $portfolio->primaryGoal()?->title(),
            'secondaryCount' => count($portfolio->goals()->secondary()),
        ];
    }
}
