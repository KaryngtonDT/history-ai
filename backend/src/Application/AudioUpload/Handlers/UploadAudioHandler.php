<?php

declare(strict_types=1);

namespace App\Application\AudioUpload\Handlers;

use App\Application\AudioUpload\Commands\UploadAudioCommand;
use App\Application\AudioUpload\DTO\UploadAudioResult;
use App\Application\AudioUpload\Ports\AudioProcessingQueueInterface;
use App\Application\AudioUpload\Ports\AudioStorageInterface;
use App\Domain\Source\AudioExtension;
use App\Domain\Source\Source;
use App\Domain\Source\SourceId;
use App\Domain\Source\SourceMetadata;
use App\Domain\Source\SourceRepositoryInterface;
use App\Domain\Source\SourceType;
use App\Domain\Source\SourceUploadSize;

final class UploadAudioHandler
{
    public function __construct(
        private readonly int $maxUploadBytes,
        private readonly AudioStorageInterface $audioStorage,
        private readonly SourceRepositoryInterface $sourceRepository,
        private readonly AudioProcessingQueueInterface $audioProcessingQueue,
    ) {
    }

    public function __invoke(UploadAudioCommand $command): UploadAudioResult
    {
        AudioExtension::fromFilename($command->originalFilename);
        SourceUploadSize::assertWithinLimit($command->fileSizeBytes, $this->maxUploadBytes);

        $audioId = SourceId::generate();
        $uploaded = Source::createUploaded(
            $audioId,
            SourceType::Audio,
            new SourceMetadata($command->originalFilename),
        );

        $storagePath = $this->audioStorage->store(
            $audioId,
            $command->temporaryPath,
            $command->originalFilename,
        );

        $queued = $uploaded
            ->withStoragePath($storagePath)
            ->queue();

        $this->sourceRepository->save($queued);
        $this->audioProcessingQueue->enqueue(
            $queued->id(),
            $command->processingMode,
            $command->strategy,
        );

        return new UploadAudioResult(
            $queued->id(),
            $queued->status(),
        );
    }
}
