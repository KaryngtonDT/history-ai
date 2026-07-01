<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

enum ShadowParticipant: string
{
    case User = 'user';
    case Shadow = 'shadow';
}
