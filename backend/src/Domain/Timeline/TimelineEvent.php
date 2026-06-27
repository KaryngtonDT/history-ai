<?php

declare(strict_types=1);

namespace App\Domain\Timeline;

final readonly class TimelineEvent
{
    public function __construct(private string $text)
    {
    }

    public function text(): string
    {
        return $this->text;
    }
}
