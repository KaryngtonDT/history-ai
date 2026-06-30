<?php

declare(strict_types=1);

namespace App\Application\TTS\Queries;

final readonly class ListVideoAudioQuery
{
    public function __construct(public string $videoId)
    {
    }
}
