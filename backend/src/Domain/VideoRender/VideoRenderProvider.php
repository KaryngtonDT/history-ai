<?php

declare(strict_types=1);

namespace App\Domain\VideoRender;

enum VideoRenderProvider: string
{
    case FFmpeg = 'ffmpeg';
    case Mock = 'mock';
}
