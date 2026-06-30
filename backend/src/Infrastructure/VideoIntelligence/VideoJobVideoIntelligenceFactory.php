<?php

declare(strict_types=1);

namespace App\Infrastructure\VideoIntelligence;

use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\VideoIntelligence\VideoAnalyzerInput;
use App\Domain\VideoIntelligence\VideoAnalyzerInterface;
use App\Domain\VideoIntelligence\VideoIntelligence;
use App\Domain\VideoIntelligence\VideoIntelligenceFactoryInterface;

final class VideoJobVideoIntelligenceFactory implements VideoIntelligenceFactoryInterface
{
    public function __construct(
        private readonly VideoAnalyzerInterface $analyzer,
        private readonly TranscriptRepositoryInterface $transcriptRepository,
        private readonly bool $gpuAvailable,
        private readonly float $estimatedVramGb,
        private readonly float $defaultDurationSeconds,
    ) {
    }

    public function fromVideoJob(VideoJob $job): VideoIntelligence
    {
        $transcript = $this->transcriptRepository->findByVideoId($job->id());
        $durationSeconds = null !== $transcript && $transcript->duration() > 0
            ? $transcript->duration()
            : $this->defaultDurationSeconds;

        return $this->analyzer->analyze(
            VideoAnalyzerInput::create(
                $this->mapLanguage($job->language()),
                $durationSeconds,
                '1920x1080',
                30.0,
                $transcript?->segmentCount() ?? 0,
                $transcript?->text() ?? '',
                $this->gpuAvailable,
                $this->estimatedVramGb,
                str_contains(strtolower($job->originalFilename()), 'slide'),
            ),
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
