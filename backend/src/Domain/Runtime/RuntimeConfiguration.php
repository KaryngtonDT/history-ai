<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

use App\Domain\Engine\EngineProfileName;
use App\Domain\Engine\SelectionMode;

final readonly class RuntimeConfiguration
{
    /**
     * @param array<string, string> $manualSelections
     * @param array<string, mixed> $customProfile
     */
    public function __construct(
        public EngineProfileName $profile,
        public SelectionMode $selectionMode,
        public array $manualSelections = [],
        public array $customProfile = [],
    ) {
    }

    public static function default(): self
    {
        return new self(EngineProfileName::Balanced, SelectionMode::Auto);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $profile = EngineProfileName::tryFrom((string) ($data['profile'] ?? '')) ?? EngineProfileName::Balanced;
        $mode = SelectionMode::tryFrom((string) ($data['selectionMode'] ?? '')) ?? SelectionMode::Auto;
        $manual = [];

        if (isset($data['manualSelections']) && is_array($data['manualSelections'])) {
            foreach ($data['manualSelections'] as $capability => $engineId) {
                if (is_string($capability) && is_string($engineId)) {
                    $manual[$capability] = $engineId;
                }
            }
        }

        $custom = isset($data['customProfile']) && is_array($data['customProfile']) ? $data['customProfile'] : [];

        return new self($profile, $mode, $manual, $custom);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'profile' => $this->profile->value,
            'selectionMode' => $this->selectionMode->value,
            'manualSelections' => $this->manualSelections,
            'customProfile' => $this->customProfile,
        ];
    }

    public function withProfile(EngineProfileName $profile): self
    {
        return new self($profile, $this->selectionMode, $this->manualSelections, $this->customProfile);
    }

    public function withSelectionMode(SelectionMode $selectionMode): self
    {
        return new self($this->profile, $selectionMode, $this->manualSelections, $this->customProfile);
    }

    /**
     * @param array<string, string> $manualSelections
     */
    public function withManualSelections(array $manualSelections): self
    {
        return new self($this->profile, $this->selectionMode, $manualSelections, $this->customProfile);
    }
}
