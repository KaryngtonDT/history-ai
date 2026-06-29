<?php

declare(strict_types=1);

namespace App\Infrastructure\Speech;

use App\Domain\Speech\TranscriptLanguage;

final class TranscriptLanguageMapper
{
    public static function fromProviderCode(string $code): TranscriptLanguage
    {
        $normalized = strtolower(trim($code));

        return match ($normalized) {
            'en', 'english' => TranscriptLanguage::English,
            'fr', 'french' => TranscriptLanguage::French,
            'de', 'german' => TranscriptLanguage::German,
            default => TranscriptLanguage::Unknown,
        };
    }
}
