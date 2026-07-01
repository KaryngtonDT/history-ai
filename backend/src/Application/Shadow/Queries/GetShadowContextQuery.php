<?php

declare(strict_types=1);

namespace App\Application\Shadow\Queries;

final readonly class GetShadowContextQuery
{
    public function __construct(
        public string $videoId,
        public float $time,
        public string $language,
        public ?string $conversationId = null,
    ) {
    }
}
