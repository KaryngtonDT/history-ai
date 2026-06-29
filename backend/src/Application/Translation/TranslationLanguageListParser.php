<?php

declare(strict_types=1);

namespace App\Application\Translation;

use App\Domain\Translation\Exception\InvalidTranslationException;
use App\Domain\Translation\TranslationLanguage;

final class TranslationLanguageListParser
{
    /**
     * @return list<TranslationLanguage>
     */
    public static function parse(string $value): array
    {
        if ('' === trim($value)) {
            return [];
        }

        /** @var list<TranslationLanguage> $languages */
        $languages = [];

        foreach (explode(',', $value) as $rawCode) {
            $code = strtolower(trim($rawCode));

            if ('' === $code) {
                continue;
            }

            $language = self::mapCode($code);

            if (null === $language) {
                throw new InvalidTranslationException(sprintf('Unsupported translation language code "%s".', $code));
            }

            $languages[] = $language;
        }

        return $languages;
    }

    private static function mapCode(string $code): ?TranslationLanguage
    {
        return match ($code) {
            'en', 'english' => TranslationLanguage::English,
            'fr', 'french' => TranslationLanguage::French,
            'de', 'german' => TranslationLanguage::German,
            'es', 'spanish' => TranslationLanguage::Spanish,
            'it', 'italian' => TranslationLanguage::Italian,
            default => null,
        };
    }
}
