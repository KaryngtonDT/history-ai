<?php

declare(strict_types=1);

namespace App\Application\YouTube\Handlers;

use App\Application\YouTube\DTO\GetYouTubeResult;
use App\Application\YouTube\DTO\PreviewYouTubeResult;
use App\Application\YouTube\Queries\PreviewYouTubeQuery;
use App\Domain\YouTube\YouTubeImporterInterface;
use App\Domain\YouTube\YouTubeUrl;

final class PreviewYouTubeHandler
{
    public function __construct(
        private readonly YouTubeImporterInterface $importer,
    ) {
    }

    public function __invoke(PreviewYouTubeQuery $query): PreviewYouTubeResult
    {
        YouTubeUrl::assertValid($query->url);
        $metadata = $this->importer->fetchMetadata($query->url);

        return new PreviewYouTubeResult($query->url, $metadata);
    }
}
