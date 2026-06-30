<?php

declare(strict_types=1);

namespace App\Application\Quality;

use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\ExecutionOptimizerInterface;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Quality\QualityEvaluatorInterface;
use App\Domain\Quality\QualityReport;
use App\Domain\Video\VideoId;
use App\Domain\VideoIntelligence\VideoIntelligenceFactoryInterface;
use App\Domain\VideoRender\FinalVideoRepositoryInterface;
use App\Domain\Video\VideoRepositoryInterface;
use Throwable;

final class VideoQualityAssessmentRunner
{
    public function __construct(
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly VideoIntelligenceFactoryInterface $videoIntelligenceFactory,
        private readonly ExecutionOptimizerInterface $executionOptimizer,
        private readonly QualityEvaluatorInterface $qualityEvaluator,
        private readonly FinalVideoRepositoryInterface $finalVideoRepository,
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly QualityReportJsonMapper $qualityReportJsonMapper,
    ) {
    }

    public function assess(VideoId $videoId, ?ExecutionOptimization $optimization = null): ?QualityReport
    {
        try {
            $job = $this->videoRepository->findById($videoId);

            if (null === $job) {
                return null;
            }

            $intelligence = $this->videoIntelligenceFactory->fromVideoJob($job);
            $optimization ??= $this->executionOptimizer->optimize($intelligence);
            $finalVideos = $this->finalVideoRepository->findAllDetailedByVideoId($videoId);
            $finalVideo = [] !== $finalVideos ? $finalVideos[0]['artifact'] : null;
            $report = $this->qualityEvaluator->evaluate($intelligence, $optimization, $finalVideo);

            $artifact = Artifact::create(
                ArtifactId::generate(),
                new ContentId($videoId->value),
                new ProcessingJobId($videoId->value),
                ArtifactType::QualityReport,
                ArtifactContent::fromString($this->qualityReportJsonMapper->toJson($report)),
            );
            $this->artifactRepository->save($artifact);

            return $report;
        } catch (Throwable) {
            return null;
        }
    }
}
