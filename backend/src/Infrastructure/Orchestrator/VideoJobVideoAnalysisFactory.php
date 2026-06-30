<?php

declare(strict_types=1);

namespace App\Infrastructure\Orchestrator;

use App\Domain\Orchestrator\VideoAnalysis;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;

final class VideoJobVideoAnalysisFactory implements \App\Domain\Orchestrator\VideoAnalysisFactoryInterface
{
    public function __construct(
        private readonly bool $gpuAvailable,
        private readonly float $estimatedVramGb,
        private readonly float $defaultDurationSeconds,
    ) {
    }

    public function fromVideoJob(VideoJob $job): VideoAnalysis
    {
        return VideoAnalysis::create(
            $this->mapLanguage($job->language()),
            $this->defaultDurationSeconds,
            '1920x1080',
            30.0,
            $this->gpuAvailable,
            $this->estimatedVramGb,
        );
    }

    private function mapLanguage(VideoLanguage $language): string
    {
        return match ($language) {
            VideoLanguage::English => 'english',
            VideoLanguage::French => 'french',
            VideoLanguage::German => 'german',
            VideoLanguage::Unknown => 'english',
        };
    }
}
