<?php

declare(strict_types=1);

namespace App\Application\VideoRender\Commands;

final readonly class GenerateVideoRenderCommand
{
    /**
     * @param list<string> $targetLanguages
     */
    public function __construct(
        public string $videoId,
        public array $targetLanguages = [],
        public ?string $provider = null,
        public ?string $format = null,
        public ?string $quality = null,
    ) {
    }
}
