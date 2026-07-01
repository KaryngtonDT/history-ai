<?php

declare(strict_types=1);

namespace App\Application\Telemetry\DTO;

final readonly class ProviderStatisticsResult
{
    /**
     * @param list<ProviderStatResult> $providers
     */
    public function __construct(public array $providers)
    {
    }
}
