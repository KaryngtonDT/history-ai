<?php

declare(strict_types=1);

namespace App\Domain\VoiceClone;

enum VoiceCloneProvider: string
{
    case OpenVoice = 'openvoice';
    case SeedVC = 'seedvc';
    case Mock = 'mock';
}
