<?php

declare(strict_types=1);

namespace App\Application\Translation;

use App\Domain\Translation\TranslationLanguage;

final class PipelineTranslationMetadataResolver
{
    /**
     * @param array<string, mixed> $metadata
     *
     * @return list<TranslationLanguage>
     */
    public function resolveLanguages(array $metadata, DefaultTranslationLanguagesProvider $fallback): array
    {
        $raw = $metadata['targetLanguages'] ?? null;

        if (!is_array($raw) || [] === $raw) {
            return $fallback->all();
        }

        $languages = [];

        foreach ($raw as $code) {
            if (!is_string($code)) {
                continue;
            }

            $parsed = TranslationLanguageListParser::parse($code);

            if ([] !== $parsed) {
                $languages[] = $parsed[0];
            }
        }

        return [] !== $languages ? $languages : $fallback->all();
    }
}
