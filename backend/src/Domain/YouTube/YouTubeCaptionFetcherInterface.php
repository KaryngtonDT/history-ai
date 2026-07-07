<?php

declare(strict_types=1);

namespace App\Domain\YouTube;

interface YouTubeCaptionFetcherInterface
{
    public function fetchOriginalCaptions(string $url, ?string $originalLanguage): ?YouTubeCaptionResult;
}
