<?php

declare(strict_types=1);

namespace App\Application\ShadowConfiguration;

final readonly class ShadowConfigurationDetection
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        public ShadowConfigurationIntent $intent,
        public array $parameters,
        public string $explanation,
        public float $confidence,
    ) {
    }
}
