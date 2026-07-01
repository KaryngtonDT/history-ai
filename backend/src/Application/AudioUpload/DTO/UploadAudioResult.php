<?php

declare(strict_types=1);

namespace App\Application\AudioUpload\DTO;

use App\Domain\Source\SourceId;
use App\Domain\Source\SourceStatus;

final readonly class UploadAudioResult
{
    public function __construct(
        public SourceId $audioId,
        public SourceStatus $status,
    ) {
    }
}
