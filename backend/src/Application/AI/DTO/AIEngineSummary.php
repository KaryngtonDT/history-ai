<?php

declare(strict_types=1);

namespace App\Application\AI\DTO;

final readonly class AIEngineSummary
{
    /**
     * @param list<AIProviderSummary> $providers
     */
    public function __construct(
        public string $engineId,
        public string $capability,
        public bool $enabled,
        public array $providers,
    ) {
    }
}
