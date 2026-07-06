<?php

declare(strict_types=1);

namespace App\Application\Runtime;

final readonly class ProvisioningSkippedEntry
{
    /**
     * @param list<string> $compatibleProviders
     */
    public function __construct(
        public string $engineId,
        public string $capability,
        public string $blockedReasonCode,
        public string $humanReason,
        public array $compatibleProviders,
        public ?string $recommendedAlternative,
        public bool $installAttempted = false,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'engineId' => $this->engineId,
            'capability' => $this->capability,
            'status' => 'blocked',
            'blockedReasonCode' => $this->blockedReasonCode,
            'humanReason' => $this->humanReason,
            'compatibleProviders' => $this->compatibleProviders,
            'recommendedAlternative' => $this->recommendedAlternative,
            'installAttempted' => $this->installAttempted,
        ];
    }
}
