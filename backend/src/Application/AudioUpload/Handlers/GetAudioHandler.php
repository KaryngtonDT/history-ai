<?php

declare(strict_types=1);

namespace App\Application\AudioUpload\Handlers;

use App\Application\AudioUpload\DTO\GetAudioResult;
use App\Application\AudioUpload\Queries\GetAudioQuery;
use App\Domain\Source\Exception\InvalidSourceException;
use App\Domain\Source\SourceId;
use App\Domain\Source\SourceRepositoryInterface;
use App\Domain\Source\SourceType;

final class GetAudioHandler
{
    public function __construct(
        private readonly SourceRepositoryInterface $sourceRepository,
    ) {
    }

    public function __invoke(GetAudioQuery $query): GetAudioResult
    {
        $source = $this->sourceRepository->findById(new SourceId($query->audioId));

        if (null === $source || SourceType::Audio !== $source->type()) {
            throw new InvalidSourceException('Audio source not found.');
        }

        return GetAudioResult::fromSource($source);
    }
}
