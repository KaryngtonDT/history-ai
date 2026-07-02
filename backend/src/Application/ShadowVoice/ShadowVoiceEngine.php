<?php

declare(strict_types=1);

namespace App\Application\ShadowVoice;

enum ShadowVoiceEngine: string
{
    case BrowserTts = 'browser_tts';
    case F5Tts = 'f5_tts';
    case Xtts = 'xtts';
    case OpenVoice = 'openvoice';
    case Future = 'future';

    public function label(): string
    {
        return match ($this) {
            self::BrowserTts => 'Browser TTS',
            self::F5Tts => 'F5-TTS',
            self::Xtts => 'XTTS',
            self::OpenVoice => 'OpenVoice',
            self::Future => 'Future Engine',
        };
    }

    public function isAvailable(): bool
    {
        return self::BrowserTts === $this;
    }
}
