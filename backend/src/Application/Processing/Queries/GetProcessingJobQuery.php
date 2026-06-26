<?php

declare(strict_types=1);

namespace App\Application\Processing\Queries;

final readonly class GetProcessingJobQuery
{
    public function __construct(
        public string $processingJobId,
    ) {
    }
}
