<?php

declare(strict_types=1);

namespace App\Domain\Source;

use App\Domain\Source\Exception\InvalidSourceException;

enum AudioExtension: string
{
    case Mp3 = 'mp3';
    case Wav = 'wav';
    case Flac = 'flac';
    case M4a = 'm4a';
    case Ogg = 'ogg';

  /**
   * @return list<string>
   */
    public static function supportedValues(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    public static function fromFilename(string $filename): self
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        foreach (self::cases() as $case) {
            if ($case->value === $extension) {
                return $case;
            }
        }

        throw new InvalidSourceException(sprintf(
            'Audio upload must use one of the supported formats: %s.',
            implode(', ', self::supportedValues()),
        ));
    }
}
