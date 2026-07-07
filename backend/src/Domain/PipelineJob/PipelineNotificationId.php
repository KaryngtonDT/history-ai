<?php

declare(strict_types=1);

namespace App\Domain\PipelineJob;

use App\Domain\PipelineJob\Exception\InvalidPipelineJobException;

final readonly class PipelineNotificationId
{
    public function __construct(public string $value)
    {
        if ('' === trim($value)) {
            throw new InvalidPipelineJobException('Pipeline notification id cannot be empty.');
        }
    }

    public static function generate(): self
    {
        return new self(PipelineJobId::generate()->value);
    }
}
