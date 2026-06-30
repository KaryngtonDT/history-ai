<?php

declare(strict_types=1);

namespace App\Application\Quality\Handlers;

use App\Application\Quality\DTO\QualityReportResult;
use App\Application\Quality\QualityReportJsonMapper;
use App\Application\Quality\Queries\GetQualityReportQuery;
use App\Application\Quality\VideoQualityAssessmentRunner;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Optimization\ExecutionOptimizerInterface;
use App\Domain\Quality\QualityEvaluatorInterface;
use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\VideoIntelligence\VideoIntelligenceFactoryInterface;
use App\Domain\VideoRender\FinalVideoRepositoryInterface;

final class GetQualityReportHandler
{
    public function __construct(
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly QualityReportJsonMapper $qualityReportJsonMapper,
        private readonly VideoIntelligenceFactoryInterface $videoIntelligenceFactory,
        private readonly ExecutionOptimizerInterface $executionOptimizer,
        private readonly QualityEvaluatorInterface $qualityEvaluator,
        private readonly FinalVideoRepositoryInterface $finalVideoRepository,
        private readonly VideoQualityAssessmentRunner $qualityAssessmentRunner,
    ) {
    }

    public function __invoke(GetQualityReportQuery $query): QualityReportResult
    {
        try {
            $videoId = new VideoId($query->videoId);
        } catch (InvalidVideoJobException) {
            throw new InvalidVideoJobException('Video not found.');
        }

        $job = $this->videoRepository->findById($videoId);

        if (null === $job) {
            throw new InvalidVideoJobException('Video not found.');
        }

        foreach ($this->artifactRepository->findByContentId(new ContentId($videoId->value)) as $artifact) {
            if (ArtifactType::QualityReport !== $artifact->type()) {
                continue;
            }

            $report = $this->qualityReportJsonMapper->fromJson($artifact->content()->value());

            return QualityReportResult::fromReport($videoId->value, $report);
        }

        $report = $this->qualityAssessmentRunner->assess($videoId);

        if (null !== $report) {
            return QualityReportResult::fromReport($videoId->value, $report);
        }

        $intelligence = $this->videoIntelligenceFactory->fromVideoJob($job);
        $optimization = $this->executionOptimizer->optimize($intelligence);
        $finalVideos = $this->finalVideoRepository->findAllDetailedByVideoId($videoId);
        $finalVideo = [] !== $finalVideos ? $finalVideos[0]['artifact'] : null;
        $fallbackReport = $this->qualityEvaluator->evaluate($intelligence, $optimization, $finalVideo);

        return QualityReportResult::fromReport($videoId->value, $fallbackReport);
    }
}
