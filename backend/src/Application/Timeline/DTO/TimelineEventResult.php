<?php

declare(strict_types=1);

namespace App\Application\Timeline\DTO;

final readonly class TimelineEventResult
{
    public function __construct(
        public string $text,
    ) {
    }
}
