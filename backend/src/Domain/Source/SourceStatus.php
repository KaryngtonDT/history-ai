<?php

declare(strict_types=1);

namespace App\Domain\Source;

enum SourceStatus: string
{
    case Uploaded = 'uploaded';
    case Queued = 'queued';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
