<?php

declare(strict_types=1);

namespace App\Application\Shadow\DTO;

use App\Domain\Shadow\ShadowVoicePreference;

final readonly class ShadowVoicePreferenceResult
{
    public function __construct(
        public string $mode,
        public ?string $manualLanguage,
    ) {
    }

    public static function fromDomain(ShadowVoicePreference $preference): self
    {
        return new self(
            mode: $preference->mode()->value,
            manualLanguage: $preference->manualLanguage()?->value,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'mode' => $this->mode,
            'manualLanguage' => $this->manualLanguage,
        ];
    }
}
