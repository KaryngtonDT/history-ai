<?php

declare(strict_types=1);

namespace App\Infrastructure\TTS;

use App\Domain\TTS\Voice;
use App\Domain\TTS\VoiceCollection;
use App\Domain\TTS\VoiceGender;
use App\Domain\TTS\VoiceLanguage;

final class F5VoiceCatalog
{
    public static function all(): VoiceCollection
    {
        return new VoiceCollection([
            Voice::create('female_01', 'Female 01', VoiceLanguage::French, VoiceGender::Female),
            Voice::create('male_01', 'Male 01', VoiceLanguage::French, VoiceGender::Male),
            Voice::create('female_en_01', 'Female EN 01', VoiceLanguage::English, VoiceGender::Female),
            Voice::create('male_en_01', 'Male EN 01', VoiceLanguage::English, VoiceGender::Male),
            Voice::create('female_de_01', 'Female DE 01', VoiceLanguage::German, VoiceGender::Female),
            Voice::create('female_es_01', 'Female ES 01', VoiceLanguage::Spanish, VoiceGender::Female),
            Voice::create('female_it_01', 'Female IT 01', VoiceLanguage::Italian, VoiceGender::Female),
        ]);
    }
}
