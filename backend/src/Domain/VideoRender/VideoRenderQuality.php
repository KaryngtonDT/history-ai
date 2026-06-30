<?php

declare(strict_types=1);

namespace App\Domain\VideoRender;

enum VideoRenderQuality: string
{
    case Preview = 'preview';
    case Standard = 'standard';
    case High = 'high';
}
