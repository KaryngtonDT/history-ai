<?php

declare(strict_types=1);

namespace App\Domain\TTS;

enum FileFormat: string
{
    case Wav = 'wav';
    case Mp3 = 'mp3';
}
