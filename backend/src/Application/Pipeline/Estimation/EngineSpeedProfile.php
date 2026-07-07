<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Estimation;

final readonly class EngineSpeedProfile
{
    public function __construct(
        public string $engineId,
        public float $realTimeFactorMin,
        public float $realTimeFactorMax,
    ) {
    }

    public static function forModel(string $model, bool $hasGpu): self
    {
        if ($hasGpu) {
            return new self($model, 0.05, 0.2);
        }

        return match ($model) {
            'large-v3', 'large-v2', 'large' => new self($model, 0.8, 2.5),
            'medium' => new self($model, 0.5, 1.5),
            'small' => new self($model, 0.3, 1.0),
            default => new self($model, 0.2, 0.8),
        };
    }
}
