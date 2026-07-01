<?php

declare(strict_types=1);

namespace App\Application\AudioUpload;

use App\Domain\Source\SourceId;

final readonly class AudioProcessingContext
{
    public function __construct(
        public SourceId $audioId,
        public string $storagePath,
        public string $title,
    ) {
    }
}
