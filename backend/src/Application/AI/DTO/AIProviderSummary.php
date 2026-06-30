<?php

declare(strict_types=1);

namespace App\Application\AI\DTO;

final readonly class AIProviderSummary
{
    public function __construct(
        public string $providerId,
        public string $displayName,
        public string $capability,
        public bool $enabled,
    ) {
    }
}
