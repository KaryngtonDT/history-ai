<?php

declare(strict_types=1);

namespace App\Application\AudioUpload\Queries;

final readonly class ListAudioQuery
{
    public function __construct(public int $limit = 20)
    {
    }
}
