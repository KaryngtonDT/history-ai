<?php

declare(strict_types=1);

namespace App\Application\Translation\Commands;

final readonly class GenerateVideoTranslationsCommand
{
    /**
     * @param list<string> $targetLanguages
     */
    public function __construct(
        public string $videoId,
        public array $targetLanguages,
        public ?string $provider = null,
    ) {
    }
}
