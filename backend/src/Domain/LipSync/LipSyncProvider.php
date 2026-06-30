<?php

declare(strict_types=1);

namespace App\Domain\LipSync;

enum LipSyncProvider: string
{
    case LatentSync = 'latentsync';
    case Wav2Lip = 'wav2lip';
    case Mock = 'mock';
}
