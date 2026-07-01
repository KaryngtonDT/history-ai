<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Shadow;

use App\Application\Shadow\DTO\ShadowVoicePreferenceResult;

final readonly class ShadowVoicePreferenceResponse
{
    public function __construct(public array $voicePreference)
    {
    }

    public static function fromResult(ShadowVoicePreferenceResult $result): self
    {
        return new self($result->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['voicePreference' => $this->voicePreference];
    }
}
