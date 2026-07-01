<?php

declare(strict_types=1);

namespace App\Domain\Source;

final readonly class SourceProcessingResult
{
    public function __construct(
        public SourceId $sourceId,
        public SourceStatus $status,
    ) {
    }
}
