<?php

declare(strict_types=1);

namespace App\Application\Shadow\SessionLearning;

use App\Domain\Shadow\SessionLearning\PedagogicalAttention;

final class AttentionDetector
{
    public function detect(int $pauseCount, int $replayCount, int $skipCount): PedagogicalAttention
    {
        $distractionScore = $pauseCount + ($replayCount * 2) + $skipCount;

        if ($distractionScore >= 6) {
            return PedagogicalAttention::Low;
        }

        if ($distractionScore >= 3) {
            return PedagogicalAttention::Medium;
        }

        return PedagogicalAttention::High;
    }
}
