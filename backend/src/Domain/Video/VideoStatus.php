<?php

declare(strict_types=1);

namespace App\Domain\Video;

enum VideoStatus: string
{
    case Uploaded = 'uploaded';
    case Queued = 'queued';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
