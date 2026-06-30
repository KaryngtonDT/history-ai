<?php

declare(strict_types=1);

namespace App\Domain\Optimization;

enum OptimizationStage: string
{
    case SpeechToText = 'speech_to_text';
    case Translation = 'translation';
    case TextToSpeech = 'text_to_speech';
    case VoiceClone = 'voice_clone';
    case LipSync = 'lip_sync';
    case VideoRender = 'video_render';

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        return self::cases();
    }
}
