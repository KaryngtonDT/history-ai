<?php

declare(strict_types=1);

namespace App\Domain\YouTube;

enum YouTubeCaptionKind: string
{
    case Manual = 'manual';
    case Auto = 'auto';
}

final readonly class YouTubeCaptionResult
{
    /**
     * @param list<array{index: int, start: float, end: float, text: string}> $segments
     */
    public function __construct(
        public YouTubeCaptionKind $kind,
        public string $language,
        public array $segments,
    ) {
    }
}
