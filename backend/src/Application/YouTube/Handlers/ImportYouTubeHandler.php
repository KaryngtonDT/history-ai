<?php

declare(strict_types=1);

namespace App\Application\YouTube\Handlers;

use App\Application\Video\Ports\VideoProcessingQueueInterface;
use App\Application\Video\Ports\VideoStorageInterface;
use App\Application\YouTube\Commands\ImportYouTubeCommand;
use App\Domain\Source\Source;
use App\Domain\Source\SourceId;
use App\Domain\Source\SourceMetadata;
use App\Domain\Source\SourceRepositoryInterface;
use App\Domain\Source\SourceType;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\YouTube\YouTubeImportResult;
use App\Domain\YouTube\YouTubeImporterInterface;
use App\Domain\YouTube\YouTubeUrl;
use App\Domain\YouTube\YouTubeVideo;
use App\Domain\YouTube\YouTubeVideoId;
use App\Domain\YouTube\YouTubeVideoRepositoryInterface;

final class ImportYouTubeHandler
{
    public function __construct(
        private readonly YouTubeImporterInterface $importer,
        private readonly string $downloadDirectory,
        private readonly VideoStorageInterface $videoStorage,
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly VideoProcessingQueueInterface $videoProcessingQueue,
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

        if ($command->queueProcessing) {
            $this->videoProcessingQueue->enqueue(
                $videoId,
                $command->processingMode,
                $command->strategy,
            );
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
