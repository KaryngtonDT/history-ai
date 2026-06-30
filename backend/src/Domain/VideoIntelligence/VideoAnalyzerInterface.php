<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

interface VideoAnalyzerInterface
{
    public function analyze(VideoAnalyzerInput $input): VideoIntelligence;
}
