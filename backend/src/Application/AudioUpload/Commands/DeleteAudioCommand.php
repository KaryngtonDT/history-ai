<?php

declare(strict_types=1);

namespace App\Application\AudioUpload\Commands;

final readonly class DeleteAudioCommand
{
    public function __construct(public string $audioId)
    {
    }
}
