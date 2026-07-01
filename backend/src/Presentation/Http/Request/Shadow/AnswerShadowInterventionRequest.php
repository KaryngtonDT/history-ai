<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Shadow;

use App\Presentation\Http\Request\Shadow\Exception\InvalidShadowRequestException;

final readonly class AnswerShadowInterventionRequest
{
    public function __construct(
        public string $answer,
        public float $time,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        $answer = $payload['answer'] ?? null;

        if (!is_string($answer) || '' === trim($answer)) {
            throw new InvalidShadowRequestException('Answer is required.');
        }

        $time = $payload['time'] ?? null;

        if (!is_numeric($time)) {
            throw new InvalidShadowRequestException('Playback time is required.');
        }

        return new self(trim($answer), (float) $time);
    }
}
