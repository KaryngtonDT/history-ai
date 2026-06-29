<?php

declare(strict_types=1);

namespace App\Application\Video\Messages;

final readonly class ProcessVideoMessage
{
    public function __construct(public string $videoId)
    {
    }
}
