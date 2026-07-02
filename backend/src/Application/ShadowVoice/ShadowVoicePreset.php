<?php

declare(strict_types=1);

namespace App\Application\ShadowVoice;

enum ShadowVoicePreset: string
{
    case Developer = 'developer';
    case LanguageTeacher = 'language_teacher';
    case Historian = 'historian';
    case Professor = 'professor';
    case Friendly = 'friendly';
    case Storyteller = 'storyteller';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Developer => 'Developer',
            self::LanguageTeacher => 'Language Teacher',
            self::Historian => 'Historian',
            self::Professor => 'Professor',
            self::Friendly => 'Friendly',
            self::Storyteller => 'Storyteller',
            self::Custom => 'Custom',
        };
    }
}
