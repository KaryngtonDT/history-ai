<?php

declare(strict_types=1);

namespace App\Application\Shadow\Commands;

final readonly class StartShadowSessionCommand
{
    public function __construct(
        public string $videoId,
        public string $targetLanguage,
        public ?string $contentId = null,
        public ?string $conversationId = null,
    ) {
    }
}
