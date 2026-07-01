<?php

declare(strict_types=1);

namespace App\Application\Shadow\Commands;

final readonly class AskShadowQuestionCommand
{
    public function __construct(
        public string $videoId,
        public string $sessionId,
        public string $question,
        public float $currentTimeSeconds,
    ) {
    }
}
