<?php

declare(strict_types=1);

namespace App\Domain\Mobile;

final readonly class MobilePreferences
{
    /** @param list<string> $categories */
    public function __construct(
        private bool $notificationsEnabled,
        private string $notificationFrequency,
        private array $categories,
        private bool $voiceEnabled,
        private string $language,
    ) {
    }

    public static function createDefault(): self
    {
        return new self(
            notificationsEnabled: true,
            notificationFrequency: 'daily',
            categories: ['missions', 'revisions', 'server'],
            voiceEnabled: true,
            language: 'en',
        );
    }

    public function notificationsEnabled(): bool
    {
        return $this->notificationsEnabled;
    }

    public function notificationFrequency(): string
    {
        return $this->notificationFrequency;
    }

    /** @return list<string> */
    public function categories(): array
    {
        return $this->categories;
    }

    public function voiceEnabled(): bool
    {
        return $this->voiceEnabled;
    }

    public function language(): string
    {
        return $this->language;
    }

    /** @param array<string, mixed> $data */
    public function withUpdates(array $data): self
    {
        $frequency = is_string($data['notificationFrequency'] ?? null)
            ? $data['notificationFrequency']
            : $this->notificationFrequency;

        if (!in_array($frequency, ['daily', 'weekly'], true)) {
            $frequency = $this->notificationFrequency;
        }

        $categories = is_array($data['categories'] ?? null)
            ? array_values(array_filter($data['categories'], 'is_string'))
            : $this->categories;

        return new self(
            is_bool($data['notificationsEnabled'] ?? null) ? $data['notificationsEnabled'] : $this->notificationsEnabled,
            $frequency,
            $categories,
            is_bool($data['voiceEnabled'] ?? null) ? $data['voiceEnabled'] : $this->voiceEnabled,
            is_string($data['language'] ?? null) ? trim($data['language']) : $this->language,
        );
    }
}
