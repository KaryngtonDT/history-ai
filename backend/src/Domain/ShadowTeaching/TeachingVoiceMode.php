<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

enum TeachingVoiceMode: string
{
    case Professor = 'professor';
    case Coach = 'coach';
    case Storyteller = 'storyteller';
    case Examiner = 'examiner';
    case Socratic = 'socratic';
}
