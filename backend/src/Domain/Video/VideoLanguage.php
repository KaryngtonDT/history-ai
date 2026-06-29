<?php

declare(strict_types=1);

namespace App\Domain\Video;

enum VideoLanguage: string
{
    case English = 'english';
    case French = 'french';
    case German = 'german';
    case Unknown = 'unknown';
}
