<?php

declare(strict_types=1);

namespace App\Application\Shadow\SessionLearning;

use App\Domain\Shadow\SessionLearning\PedagogicalConfidence;

final class ConfidenceDetector
{
    public function detect(
        int $repeatedQuestionCount,
        int $replayCount,
        int $challengeSuccessCount,
        int $slowResponseCount,
    ): PedagogicalConfidence {
        if ($repeatedQuestionCount >= 2 || ($replayCount >= 2 && $slowResponseCount >= 2)) {
            return PedagogicalConfidence::Struggling;
        }

        if ($challengeSuccessCount >= 2 && $slowResponseCount <= 1) {
            return PedagogicalConfidence::Growing;
        }

        return PedagogicalConfidence::Stable;
    }
}
