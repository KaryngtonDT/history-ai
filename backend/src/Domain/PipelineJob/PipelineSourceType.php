<?php

declare(strict_types=1);

namespace App\Domain\PipelineJob;

enum PipelineSourceType: string
{
    case Video = 'video';
    case Audio = 'audio';
    case Youtube = 'youtube';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
