<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Commands;

final readonly class SavePipelineConfigurationCommand
{
    /**
     * @param list<array{stage: string, providerId: string}> $stages
     */
    public function __construct(
        public array $stages,
    ) {
    }
}
