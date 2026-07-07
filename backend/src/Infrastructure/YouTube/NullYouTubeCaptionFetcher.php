<?php

declare(strict_types=1);

namespace App\Infrastructure\YouTube;

use App\Domain\YouTube\YouTubeCaptionFetcherInterface;
use App\Domain\YouTube\YouTubeCaptionResult;

/**
 * Test/dev fetcher: never returns captions so functional tests use the local STT path.
 */
final class NullYouTubeCaptionFetcher implements YouTubeCaptionFetcherInterface
{
    public function fetchOriginalCaptions(string $url, ?string $originalLanguage): ?YouTubeCaptionResult
    {
        return null;
    }
}
