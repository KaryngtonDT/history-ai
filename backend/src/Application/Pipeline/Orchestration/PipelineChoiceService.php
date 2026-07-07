<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Orchestration;

use App\Application\Pipeline\Estimation\TranscriptionDurationEstimator;
use App\Application\Speech\TranscriptJsonMapper;
use App\Application\Video\Messages\ProcessVideoMessage;
use App\Application\Video\Ports\VideoProcessingQueueInterface;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobId;
use App\Domain\PipelineJob\PipelineJobRepositoryInterface;
use App\Domain\PipelineJob\PipelineJobStatus;
use App\Domain\PipelineJob\PipelineSourceType;
use App\Domain\PipelineJob\TranscriptSource;
use App\Domain\PipelineJob\TranscriptUserChoice;
use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptId;
use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Speech\TranscriptMetadata;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\YouTube\YouTubeCaptionKind;
use App\Domain\YouTube\YouTubeCaptionResult;
use DateTimeImmutable;

final class PipelineChoiceService
{
    public function __construct(
        private readonly TranscriptRepositoryInterface $transcriptRepository,
        private readonly TranscriptJsonMapper $transcriptJsonMapper,
        private readonly PipelineJobRepositoryInterface $jobRepository,
        private readonly PipelineNotificationService $notificationService,
    ) {
    }

    public function applyYoutubeTranscript(
        PipelineJob $job,
        YouTubeCaptionResult $captions,
    ): PipelineJob {
        $videoId = new VideoId($job->videoId() ?? $job->sourceId());
        $source = YouTubeCaptionKind::Manual === $captions->kind
            ? TranscriptSource::YoutubeOriginalCaptions
            : TranscriptSource::YoutubeOriginalAutoCaptions;

        $segments = [];

        foreach ($captions->segments as $segment) {
            $segments[] = TranscriptSegment::create(
                (int) $segment['index'],
                (float) $segment['start'],
                (float) $segment['end'],
                $segment['text'],
            );
        }

        $transcript = Transcript::create(
            TranscriptId::generate(),
            TranscriptLanguage::tryFrom($captions->language) ?? TranscriptLanguage::Unknown,
            new TranscriptSegmentCollection($segments),
        );

        $this->transcriptRepository->save($videoId, $transcript, new TranscriptMetadata(
            transcriptSource: $source,
            sourceLanguage: $captions->language,
            confidence: YouTubeCaptionKind::Manual === $captions->kind ? 0.95 : 0.75,
            generatedAt: new DateTimeImmutable(),
            selectedByUser: true,
            originalCaptionAvailable: true,
            userChoice: TranscriptUserChoice::YoutubeTranscript,
        ));

        $updated = $job->applyUserChoice($source);
        $this->jobRepository->save($updated);
        $this->notificationService->notifyStageCompleted($updated);

        return $updated;
    }
}
