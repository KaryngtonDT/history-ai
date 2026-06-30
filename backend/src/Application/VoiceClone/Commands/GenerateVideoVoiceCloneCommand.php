<?php

declare(strict_types=1);

namespace App\Application\VoiceClone\Commands;

final readonly class GenerateVideoVoiceCloneCommand
{
    /**
     * @param list<string> $targetLanguages
     */
    public function __construct(
        public string $videoId,
        public array $targetLanguages = [],
        public ?string $provider = null,
        public ?string $voiceMode = null,
    ) {
    }
}
