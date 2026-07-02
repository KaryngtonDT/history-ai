<?php

declare(strict_types=1);

namespace App\Application\Shadow\SessionLearning;

use App\Domain\Shadow\SessionLearning\PedagogicalPace;

final class PaceDetector
{
    public function detect(int $fastResponseCount, int $slowResponseCount, int $pauseCount): PedagogicalPace
    {
        if ($fastResponseCount >= 3 && $slowResponseCount <= 1) {
            return PedagogicalPace::Fast;
        }

        if ($slowResponseCount >= 3 || $pauseCount >= 5) {
            return PedagogicalPace::Slow;
        }

        return PedagogicalPace::Normal;
    }
}
