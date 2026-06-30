<?php

declare(strict_types=1);

namespace App\Domain\Review;

enum ReviewCategory: string
{
    case Translation = 'translation';
    case VoiceClone = 'voice_clone';
    case LipSync = 'lip_sync';
    case Rendering = 'rendering';
    case Overall = 'overall';
}
