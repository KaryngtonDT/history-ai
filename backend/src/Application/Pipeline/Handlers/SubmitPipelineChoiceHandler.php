<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Handlers;

use App\Application\Pipeline\Estimation\TranscriptionDurationEstimator;
use App\Application\Pipeline\Orchestration\PipelineChoiceService;
use App\Application\Pipeline\Orchestration\PipelineOrchestrator;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJobRepositoryInterface;
use App\Domain\PipelineJob\TranscriptUserChoice;
use App\Domain\Video\VideoId;
use App\Domain\YouTube\YouTubePendingCaptionStoreInterface;

final class SubmitPipelineChoiceHandler
{
    public function __construct(
        private readonly PipelineJobRepositoryInterface $jobRepository,
        private readonly PipelineOrchestrator $orchestrator,
        private readonly PipelineChoiceService $choiceService,
        private readonly YouTubePendingCaptionStoreInterface $pendingCaptionStore,
        private readonly TranscriptionDurationEstimator $durationEstimator,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $sourceId, string $stage, string $choice): array
    {
        $stageType = PipelineStageType::tryFrom($stage);

        if (null === $stageType) {
            throw new \InvalidArgumentException('Invalid pipeline stage.');
        }

        $userChoice = TranscriptUserChoice::tryFrom($choice);

        if (null === $userChoice) {
            throw new \InvalidArgumentException('Invalid choice.');
        }

        $job = $this->jobRepository->findActiveBySourceAndStage($sourceId, $stageType);

        if (null === $job) {
            throw new \RuntimeException('No pipeline job waiting for choice.');
        }

        if (TranscriptUserChoice::YoutubeTranscript === $userChoice) {
            $captions = $this->pendingCaptionStore->load($sourceId);

            if (null === $captions) {
                throw new \RuntimeException('Pending YouTube captions not found.');
            }

            $updated = $this->choiceService->applyYoutubeTranscript($job, $captions);
            $this->pendingCaptionStore->clear($sourceId);

            return $this->orchestrator->serializeJob($updated);
        }

        $estimate = $this->durationEstimator->estimateForVideo(new VideoId($sourceId));
        $updated = $this->orchestrator->beginLocalStt($job->jobId(), $estimate['message']);
        $this->pendingCaptionStore->clear($sourceId);

        return $this->orchestrator->serializeJob($updated);
    }
}
