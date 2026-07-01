<?php

declare(strict_types=1);

namespace App\Application\AudioUpload\Handlers;

use App\Application\AudioUpload\Commands\DeleteAudioCommand;
use App\Application\AudioUpload\Ports\AudioStorageInterface;
use App\Domain\Source\Exception\InvalidSourceException;
use App\Domain\Source\SourceId;
use App\Domain\Source\SourceRepositoryInterface;
use App\Domain\Source\SourceType;

final class DeleteAudioHandler
{
    public function __construct(
        private readonly SourceRepositoryInterface $sourceRepository,
        private readonly AudioStorageInterface $audioStorage,
    ) {
    }

    public function __invoke(DeleteAudioCommand $command): void
    {
        $audioId = new SourceId($command->audioId);
        $source = $this->sourceRepository->findById($audioId);

        if (null === $source || SourceType::Audio !== $source->type()) {
            throw new InvalidSourceException('Audio source not found.');
        }

        $storagePath = $source->storagePath();

        if (null !== $storagePath && '' !== trim($storagePath)) {
            $this->audioStorage->delete($storagePath);
        }

        $this->sourceRepository->delete($audioId);
    }
}
