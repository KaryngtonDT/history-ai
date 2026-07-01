<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Shadow;

use App\Presentation\Http\Request\Shadow\Exception\InvalidShadowRequestException;

final readonly class AskShadowQuestionRequest
{
    public function __construct(
        public string $question,
        public float $time,
        public ?string $interfaceLanguage = null,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        $question = $payload['question'] ?? null;

        if (!is_string($question) || '' === trim($question)) {
            throw new InvalidShadowRequestException('Question is required.');
        }

        $time = $payload['time'] ?? null;

        if (!is_numeric($time)) {
            throw new InvalidShadowRequestException('Playback time is required.');
        }

        $interfaceLanguage = $payload['interfaceLanguage'] ?? null;

        if (null !== $interfaceLanguage && !is_string($interfaceLanguage)) {
            throw new InvalidShadowRequestException('Interface language must be a string.');
        }

        return new self(trim($question), (float) $time, is_string($interfaceLanguage) ? trim($interfaceLanguage) : null);
    }
}
