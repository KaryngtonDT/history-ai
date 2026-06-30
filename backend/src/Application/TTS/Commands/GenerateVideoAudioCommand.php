<?php

declare(strict_types=1);

namespace App\Application\TTS\Commands;

final readonly class GenerateVideoAudioCommand
{
    /**
     * @param list<string> $targetLanguages
     */
    public function __construct(
        public string $videoId,
        public array $targetLanguages,
        public ?string $provider = null,
        public ?string $voiceId = null,
    ) {
    }
}
