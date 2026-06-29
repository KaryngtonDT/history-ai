<?php

declare(strict_types=1);

namespace App\Application\Translation;

use App\Domain\Translation\TranslationLanguage;

final class DefaultTranslationLanguagesProvider
{
    /** @var list<TranslationLanguage> */
    private array $languages;

    public function __construct(string $configuredLanguages)
    {
        $this->languages = TranslationLanguageListParser::parse($configuredLanguages);
    }

    /**
     * @return list<TranslationLanguage>
     */
    public function all(): array
    {
        return $this->languages;
    }
}
