<?php

declare(strict_types=1);

namespace App\Application\VoiceClone\Queries;

final readonly class ListVideoVoiceCloneQuery
{
    public function __construct(public string $videoId)
    {
    }
}
