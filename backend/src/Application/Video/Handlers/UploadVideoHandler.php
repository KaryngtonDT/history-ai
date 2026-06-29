<?php

declare(strict_types=1);

namespace App\Application\Video\Handlers;

use App\Application\Video\Commands\UploadVideoCommand;
use App\Application\Video\DTO\UploadVideoResult;
use App\Domain\Video\VideoExtension;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoUploadSize;

final class UploadVideoHandler
{
    public function __construct(
        private readonly int $maxUploadBytes,
    ) {
    }

    public function __invoke(UploadVideoCommand $command): UploadVideoResult
    {
        VideoExtension::fromFilename($command->originalFilename);
        VideoUploadSize::assertWithinLimit($command->fileSizeBytes, $this->maxUploadBytes);

        $job = VideoJob::createUploaded(
            VideoId::generate(),
            $command->originalFilename,
            VideoLanguage::Unknown,
        );

        return new UploadVideoResult(
            $job->id(),
            $job->status(),
        );
    }
}
