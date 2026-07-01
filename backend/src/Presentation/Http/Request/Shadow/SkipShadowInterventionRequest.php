<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Shadow;

use App\Presentation\Http\Request\Shadow\Exception\InvalidShadowRequestException;

final readonly class SkipShadowInterventionRequest
{
    public function __construct(public float $time)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        $time = $payload['time'] ?? null;

        if (!is_numeric($time)) {
            throw new InvalidShadowRequestException('Playback time is required.');
        }

        return new self((float) $time);
    }
}
