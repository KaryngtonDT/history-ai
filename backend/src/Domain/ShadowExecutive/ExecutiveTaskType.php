<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

enum ExecutiveTaskType: string
{
    case Review = 'review';
    case Mission = 'mission';
    case Watch = 'watch';
    case Exercise = 'exercise';
    case Checkpoint = 'checkpoint';
    case Pause = 'pause';
}
