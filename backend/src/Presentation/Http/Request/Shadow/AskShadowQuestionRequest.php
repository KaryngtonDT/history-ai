<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Shadow;

use App\Presentation\Http\Request\Shadow\Exception\InvalidShadowRequestException;

final readonly class AskShadowQuestionRequest
{
    public function __construct(
        public string $question,
        public float $time,
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

        return new self(trim($question), (float) $time);
    }
}
