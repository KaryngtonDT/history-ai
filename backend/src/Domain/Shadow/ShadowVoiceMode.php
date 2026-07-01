<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

enum ShadowVoiceMode: string
{
    case SameAsInterface = 'same_as_interface';
    case SameAsTargetLanguage = 'same_as_target_language';
    case Manual = 'manual';
}
