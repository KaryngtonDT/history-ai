<?php

declare(strict_types=1);

namespace App\Infrastructure\Translation;

use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Translation\TranslationLanguage;

final class TranscriptLanguageMapper
{
    public static function toTranslationLanguage(TranscriptLanguage $language): TranslationLanguage
    {
        return match ($language) {
            TranscriptLanguage::English => TranslationLanguage::English,
            TranscriptLanguage::French => TranslationLanguage::French,
            TranscriptLanguage::German => TranslationLanguage::German,
            default => TranslationLanguage::Unknown,
        };
    }
}
