<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

use App\Domain\Video\VideoJob;

interface VideoIntelligenceFactoryInterface
{
    public function fromVideoJob(VideoJob $job): VideoIntelligence;
}
