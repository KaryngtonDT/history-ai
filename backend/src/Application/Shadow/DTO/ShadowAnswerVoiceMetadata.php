<?php

declare(strict_types=1);

namespace App\Application\Shadow\DTO;

use App\Domain\Shadow\ShadowVoiceLanguage;

final readonly class ShadowAnswerVoiceMetadata
{
    public function __construct(
        public ShadowVoiceLanguage $answerLanguage,
        public ShadowVoiceLanguage $speechLanguage,
        public bool $fallbackUsed,
        public string $reason,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'answerLanguage' => $this->answerLanguage->value,
            'speechLanguage' => $this->speechLanguage->value,
            'fallbackUsed' => $this->fallbackUsed,
            'reason' => $this->reason,
        ];
    }
}
