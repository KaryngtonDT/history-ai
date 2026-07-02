<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

enum ShadowPronunciationPolicy: string
{
    case American = 'american';
    case British = 'british';
    case French = 'french';
    case German = 'german';
    case Swiss = 'swiss';
    case Canadian = 'canadian';
}
