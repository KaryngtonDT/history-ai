<?php

declare(strict_types=1);

namespace App\Domain\Hardware;

final readonly class HardwareDetectionReport
{
    public function __construct(
        public HardwareProfile $profile,
        public HardwareCapability $capabilities,
        public \DateTimeImmutable $detectedAt,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'profile' => $this->profile->toArray(),
            'capabilities' => $this->capabilities->toArray(),
            'detectedAt' => $this->detectedAt->format(DATE_ATOM),
        ];
    }
}
