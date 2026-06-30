<?php

declare(strict_types=1);

namespace App\Domain\VideoRender;

enum VideoRenderFormat: string
{
    case MP4 = 'mp4';
    case WEBM = 'webm';
}
