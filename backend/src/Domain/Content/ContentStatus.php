<?php

declare(strict_types=1);

namespace App\Domain\Content;

enum ContentStatus: string
{
    case Draft = 'draft';
    case Imported = 'imported';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
