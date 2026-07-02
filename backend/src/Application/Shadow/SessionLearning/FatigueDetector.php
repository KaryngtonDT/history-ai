<?php

declare(strict_types=1);

namespace App\Application\Shadow\SessionLearning;

use App\Domain\Shadow\SessionLearning\PedagogicalFatigue;

final class FatigueDetector
{
    public function detect(int $pauseCount, int $questionCount, float $sessionMinutes): PedagogicalFatigue
    {
        $loadScore = $pauseCount + (int) floor($questionCount / 2);

        if ($sessionMinutes >= 25.0 || $loadScore >= 8) {
            return PedagogicalFatigue::High;
        }

        if ($sessionMinutes >= 12.0 || $loadScore >= 4) {
            return PedagogicalFatigue::Medium;
        }

        return PedagogicalFatigue::Low;
    }
}
