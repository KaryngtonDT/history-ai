<?php

declare(strict_types=1);

namespace App\Application\LipSync\Commands;

final readonly class GenerateVideoLipSyncCommand
{
    /**
     * @param list<string> $targetLanguages
     */
    public function __construct(
        public string $videoId,
        public array $targetLanguages = [],
        public ?string $provider = null,
    ) {
    }
}
