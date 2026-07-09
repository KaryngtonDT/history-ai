<?php

declare(strict_types=1);

namespace App\Domain\PipelineJob;

final readonly class PipelineJobProgressDetail
{
    /**
     * @param array<string, mixed>|null $extra
     */
    public function __construct(
        public ?string $checkpoint = null,
        public ?int $currentSegment = null,
        public ?int $totalSegments = null,
        public ?int $audioProcessedSeconds = null,
        public ?int $audioTotalSeconds = null,
        public ?float $processingSpeedRatio = null,
        public ?string $engineVersion = null,
        public ?string $workerId = null,
        public ?string $dockerContainer = null,
        public ?array $extra = null,
    ) {
    }

    /**
     * @param array<string, mixed>|null $data
     */
    public static function fromArray(?array $data): ?self
    {
        if (null === $data || [] === $data) {
            return null;
        }

        return new self(
            checkpoint: isset($data['checkpoint']) ? (string) $data['checkpoint'] : null,
            currentSegment: isset($data['currentSegment']) ? (int) $data['currentSegment'] : null,
            totalSegments: isset($data['totalSegments']) ? (int) $data['totalSegments'] : null,
            audioProcessedSeconds: isset($data['audioProcessedSeconds']) ? (int) $data['audioProcessedSeconds'] : null,
            audioTotalSeconds: isset($data['audioTotalSeconds']) ? (int) $data['audioTotalSeconds'] : null,
            processingSpeedRatio: isset($data['processingSpeedRatio']) ? (float) $data['processingSpeedRatio'] : null,
            engineVersion: isset($data['engineVersion']) ? (string) $data['engineVersion'] : null,
            workerId: isset($data['workerId']) ? (string) $data['workerId'] : null,
            dockerContainer: isset($data['dockerContainer']) ? (string) $data['dockerContainer'] : null,
            extra: is_array($data['extra'] ?? null) ? $data['extra'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'checkpoint' => $this->checkpoint,
            'currentSegment' => $this->currentSegment,
            'totalSegments' => $this->totalSegments,
            'audioProcessedSeconds' => $this->audioProcessedSeconds,
            'audioTotalSeconds' => $this->audioTotalSeconds,
            'processingSpeedRatio' => $this->processingSpeedRatio,
            'engineVersion' => $this->engineVersion,
            'workerId' => $this->workerId,
            'dockerContainer' => $this->dockerContainer,
            'extra' => $this->extra,
        ], static fn (mixed $value): bool => null !== $value);
    }

    /**
     * @param array<string, mixed> $patch
     */
    public function merge(array $patch): self
    {
        $merged = [...$this->toArray(), ...$patch];

        return self::fromArray($merged) ?? new self();
    }
}
