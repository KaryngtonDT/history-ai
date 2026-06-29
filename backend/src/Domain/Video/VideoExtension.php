<?php

declare(strict_types=1);

namespace App\Domain\Video;

use App\Domain\Video\Exception\InvalidVideoJobException;

enum VideoExtension: string
{
    case Mp4 = 'mp4';
    case Mov = 'mov';
    case Mkv = 'mkv';

    public static function fromFilename(string $filename): self
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        foreach (self::cases() as $case) {
            if ($case->value === $extension) {
                return $case;
            }
        }

        throw new InvalidVideoJobException(
            'Video upload must use one of the supported formats: mp4, mov, mkv.',
        );
    }
}
