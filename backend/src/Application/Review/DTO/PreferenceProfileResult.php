<?php

declare(strict_types=1);

namespace App\Application\Review\DTO;

final readonly class PreferenceProfileResult
{
    /**
     * @param list<string> $explanationLines
     */
    public function __construct(
        public string $translationStyle,
        public string $voiceStability,
        public string $renderingPreset,
        public string $lipSyncStrength,
        public string $latestComment,
        public int $reviewCount,
        public array $explanationLines,
    ) {
    }
}
