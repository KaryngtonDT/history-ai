<?php

declare(strict_types=1);

namespace App\Application\AudioUpload\Queries;

final readonly class GetAudioQuery
{
    public function __construct(public string $audioId)
    {
    }
}
