<?php

declare(strict_types=1);

namespace App\Application\YouTube\Handlers;

use App\Application\Pipeline\Estimation\TranscriptionDurationEstimator;
use App\Application\Pipeline\Orchestration\PipelineOrchestrator;
use App\Application\Video\Ports\VideoStorageInterface;
use App\Application\YouTube\Commands\ImportYouTubeCommand;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJobId;
use App\Domain\PipelineJob\PipelineSourceType;
use App\Domain\Source\Source;
use App\Domain\Source\SourceId;
use App\Domain\Source\SourceMetadata;
use App\Domain\Source\SourceRepositoryInterface;
use App\Domain\Source\SourceType;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\YouTube\YouTubeCaptionFetcherInterface;
use App\Domain\YouTube\YouTubeImportResult;
use App\Domain\YouTube\YouTubeImporterInterface;
use App\Domain\YouTube\YouTubeUrl;
use App\Domain\YouTube\YouTubeVideo;
use App\Domain\YouTube\YouTubeVideoId;
use App\Domain\YouTube\YouTubeVideoRepositoryInterface;
use App\Infrastructure\YouTube\YouTubePendingCaptionStore;

final class ImportYouTubeHandler
{
    public function __construct(
        private readonly YouTubeImporterInterface $importer,
        private readonly YouTubeCaptionFetcherInterface $captionFetcher,
        private readonly YouTubePendingCaptionStore $pendingCaptionStore,
        private readonly PipelineOrchestrator $pipelineOrchestrator,
        private readonly TranscriptionDurationEstimator $durationEstimator,
        private readonly string $downloadDirectory,
        private readonly VideoStorageInterface $videoStorage,
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly YouTubeVideoRepositoryInterface $youtubeVideoRepository,
        private readonly SourceRepositoryInterface $sourceRepository,
    ) {
    }

    public function __invoke(ImportYouTubeCommand $command): YouTubeImportResult
    {
        YouTubeUrl::assertValid($command->url);

        $youtubeId = YouTubeVideoId::generate();
        $videoId = VideoId::generate();

        $download = $this->importer->download($command->url, $this->downloadDirectory);

        $storagePath = $this->videoStorage->store(
            $videoId,
            $download->filePath,
            $download->originalFilename,
        );

        $queued = VideoJob::createUploaded(
            $videoId,
            $download->originalFilename,
            $this->resolveLanguage($download->metadata->language),
        )
            ->withStoragePath($storagePath)
            ->queue();

        $this->videoRepository->save($queued);

        $source = Source::createUploaded(
            new SourceId($youtubeId->value),
            SourceType::Youtube,
            new SourceMetadata(
                $download->originalFilename,
                $download->metadata->title,
                $download->metadata->language,
            ),
        )
            ->withStoragePath($storagePath)
            ->queue();

        $this->sourceRepository->save($source);

        $youtubeVideo = YouTubeVideo::create(
            $youtubeId,
            $command->url,
            $download->metadata,
            $videoId,
        );

        $this->youtubeVideoRepository->save($youtubeVideo);

        $sourceKey = $videoId->value;
        $pipelineJob = $this->pipelineOrchestrator->getOrCreateJob(
            $sourceKey,
            PipelineStageType::SpeechToText,
            PipelineSourceType::Youtube,
            $videoId->value,
            'faster_whisper',
            'large-v3',
        );

        $captions = $this->captionFetcher->fetchOriginalCaptions(
            $command->url,
            $download->metadata->language,
        );

        if (null !== $captions) {
            $this->pendingCaptionStore->save($videoId->value, $captions);
            $this->pipelineOrchestrator->requireUserTranscriptChoice($pipelineJob->jobId());
        } else {
            $estimate = $this->durationEstimator->estimateForVideo($videoId);
            $this->pipelineOrchestrator->beginLocalStt($pipelineJob->jobId(), $estimate['message']);
        }

        if ($command->queueProcessing) {
            // Legacy explicit auto-pipeline only when caller opts in.
        }

        return new YouTubeImportResult(
            $youtubeId,
            $videoId,
            $download->metadata,
            $queued->status(),
            $command->url,
        );
    }

    private function resolveLanguage(?string $language): VideoLanguage
    {
        if (null === $language || '' === trim($language)) {
            return VideoLanguage::Unknown;
        }

        $normalized = strtolower(substr(trim($language), 0, 2));

        return match ($normalized) {
            'en' => VideoLanguage::English,
            'fr' => VideoLanguage::French,
            'de' => VideoLanguage::German,
            default => VideoLanguage::Unknown,
        };
    }
}
