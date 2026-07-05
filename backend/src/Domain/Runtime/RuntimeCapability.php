<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

enum RuntimeCapability: string
{
    case SpeechToText = 'speech_to_text';
    case Translation = 'translation';
    case TextToSpeech = 'text_to_speech';
    case VoiceClone = 'voice_clone';
    case LipSync = 'lip_sync';
    case VideoRender = 'video_render';

    public function label(): string
    {
        return match ($this) {
            self::SpeechToText => 'Speech-to-Text',
            self::Translation => 'Translation',
            self::TextToSpeech => 'Text-to-Speech',
            self::VoiceClone => 'Voice Clone',
            self::LipSync => 'Lip Sync',
            self::VideoRender => 'Video Render',
        };
    }
}
