<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

enum ShadowVoiceLanguage: string
{
    case English = 'en';
    case French = 'fr';
    case German = 'de';

    public function label(): string
    {
        return match ($this) {
            self::English => 'English',
            self::French => 'French',
            self::German => 'German',
        };
    }

    public function bcp47(): string
    {
        return match ($this) {
            self::English => 'en-US',
            self::French => 'fr-FR',
            self::German => 'de-DE',
        };
    }

    public static function fallback(): self
    {
        return self::English;
    }

    public static function tryFromTargetLanguage(string $targetLanguage): ?self
    {
        $normalized = strtolower(trim($targetLanguage));

        return match (true) {
            in_array($normalized, ['en', 'english', 'anglais'], true) => self::English,
            in_array($normalized, ['fr', 'french', 'francais', 'français'], true) => self::French,
            in_array($normalized, ['de', 'german', 'deutsch', 'allemand'], true) => self::German,
            default => null,
        };
    }

    public static function fromString(string $value): self
    {
        $normalized = strtolower(trim($value));

        return self::tryFrom($normalized)
            ?? self::tryFromTargetLanguage($normalized)
            ?? throw new Exception\InvalidShadowSessionException(
                sprintf('Unsupported Shadow voice language "%s".', $value),
            );
    }
}
