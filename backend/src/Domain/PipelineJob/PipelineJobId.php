<?php

declare(strict_types=1);

namespace App\Domain\PipelineJob;

use App\Domain\PipelineJob\Exception\InvalidPipelineJobException;

final readonly class PipelineJobId
{
    public function __construct(public string $value)
    {
        if ('' === trim($value)) {
            throw new InvalidPipelineJobException('Pipeline job id cannot be empty.');
        }
    }

    public static function generate(): self
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return new self(vsprintf(
            '%s%s-%s-%s-%s-%s%s%s',
            str_split(bin2hex($bytes), 4),
        ));
    }
}
