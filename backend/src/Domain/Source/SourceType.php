<?php

declare(strict_types=1);

namespace App\Domain\Source;

enum SourceType: string
{
    case Video = 'video';
    case Audio = 'audio';
    case Pdf = 'pdf';
    case Youtube = 'youtube';

    public function label(): string
    {
        return match ($this) {
            self::Video => 'Video',
            self::Audio => 'Audio',
            self::Pdf => 'PDF',
            self::Youtube => 'YouTube',
        };
    }

    public function isConnectorImplemented(): bool
    {
        return self::Audio === $this || self::Youtube === $this;
    }
}
