<?php

declare(strict_types=1);

namespace App\Domain\Quality;

enum QualityCategory: string
{
    case Audio = 'audio';
    case Translation = 'translation';
    case VoiceClone = 'voice_clone';
    case LipSync = 'lip_sync';
    case Rendering = 'rendering';
    case Overall = 'overall';

    /**
     * @return list<self>
     */
    public static function scored(): array
    {
        return [
            self::Audio,
            self::Translation,
            self::VoiceClone,
            self::LipSync,
            self::Rendering,
        ];
    }
}
