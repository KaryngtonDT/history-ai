<?php

declare(strict_types=1);

namespace App\Application\Video\Handlers;

use App\Application\Video\Commands\UploadVideoCommand;
use App\Application\Video\DTO\UploadVideoResult;
use App\Application\Video\Ports\VideoProcessingQueueInterface;
use App\Application\Video\Ports\VideoStorageInterface;
use App\Domain\Video\VideoExtension;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\Video\VideoUploadSize;

final class UploadVideoHandler
{
    public function __construct(
        private readonly int $maxUploadBytes,
        private readonly VideoStorageInterface $videoStorage,
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly VideoProcessingQueueInterface $videoProcessingQueue,
    ) {
    }

    public function __invoke(UploadVideoCommand $command): UploadVideoResult
    {
        VideoExtension::fromFilename($command->originalFilename);
        VideoUploadSize::assertWithinLimit($command->fileSizeBytes, $this->maxUploadBytes);

        $videoId = VideoId::generate();
        $uploaded = VideoJob::createUploaded(
            $videoId,
            $command->originalFilename,
            VideoLanguage::Unknown,
        );

        $storagePath = $this->videoStorage->store(
            $videoId,
            $command->temporaryPath,
            $command->originalFilename,
        );

        $queued = $uploaded
            ->withStoragePath($storagePath)
            ->queue();

        $this->videoRepository->save($queued);
        $this->videoProcessingQueue->enqueue(
            $queued->id(),
            $command->processingMode,
            $command->strategy,
        );

        return new UploadVideoResult(
            $queued->id(),
            $queued->status(),
        );
    }
}
