<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

use App\Domain\Engine\CapabilitySelectionMode;
use App\Domain\Engine\EngineProfileName;
use App\Domain\Engine\SelectionMode;

final readonly class RuntimeConfiguration
{
    /**
     * @param array<string, string> $manualSelections
     * @param array<string, string> $capabilityModes
     * @param array<string, string> $lockedSelections
     * @param list<string>          $disabledEngines
     * @param array<string, mixed>  $customProfile
     */
    public function __construct(
        public EngineProfileName $profile,
        public SelectionMode $selectionMode,
        public array $manualSelections = [],
        public array $capabilityModes = [],
        public array $lockedSelections = [],
        public array $disabledEngines = [],
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
        $manual = self::stringMap($data['manualSelections'] ?? []);
        $capabilityModes = self::stringMap($data['capabilityModes'] ?? []);
        $locked = self::stringMap($data['lockedSelections'] ?? []);
        $disabled = [];

        if (isset($data['disabledEngines']) && is_array($data['disabledEngines'])) {
            foreach ($data['disabledEngines'] as $engineId) {
                if (is_string($engineId) && '' !== $engineId) {
                    $disabled[] = $engineId;
                }
            }
        }

        $custom = isset($data['customProfile']) && is_array($data['customProfile']) ? $data['customProfile'] : [];

        return new self($profile, $mode, $manual, $capabilityModes, $locked, $disabled, $custom);
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
            'capabilityModes' => $this->capabilityModes,
            'lockedSelections' => $this->lockedSelections,
            'disabledEngines' => $this->disabledEngines,
            'customProfile' => $this->customProfile,
        ];
    }

    public function capabilityMode(string $capability): CapabilitySelectionMode
    {
        $mode = $this->capabilityModes[$capability] ?? null;
        if (is_string($mode)) {
            return CapabilitySelectionMode::tryFrom($mode) ?? $this->globalCapabilityMode();
        }

        return $this->globalCapabilityMode();
    }

    public function globalCapabilityMode(): CapabilitySelectionMode
    {
        return match ($this->selectionMode) {
            SelectionMode::Manual => CapabilitySelectionMode::Manual,
            SelectionMode::Auto, SelectionMode::Profile, SelectionMode::Rules => CapabilitySelectionMode::Auto,
        };
    }

    public function isEngineDisabled(string $engineId): bool
    {
        return in_array($engineId, $this->disabledEngines, true);
    }

    public function withProfile(EngineProfileName $profile): self
    {
        return new self(
            $profile,
            $this->selectionMode,
            $this->manualSelections,
            $this->capabilityModes,
            $this->lockedSelections,
            $this->disabledEngines,
            $this->customProfile,
        );
    }

    public function withSelectionMode(SelectionMode $selectionMode): self
    {
        return new self(
            $this->profile,
            $selectionMode,
            $this->manualSelections,
            $this->capabilityModes,
            $this->lockedSelections,
            $this->disabledEngines,
            $this->customProfile,
        );
    }

    /**
     * @param array<string, string> $manualSelections
     */
    public function withManualSelections(array $manualSelections): self
    {
        return new self(
            $this->profile,
            $this->selectionMode,
            $manualSelections,
            $this->capabilityModes,
            $this->lockedSelections,
            $this->disabledEngines,
            $this->customProfile,
        );
    }

    /**
     * @param array<string, string> $capabilityModes
     */
    public function withCapabilityModes(array $capabilityModes): self
    {
        return new self(
            $this->profile,
            $this->selectionMode,
            $this->manualSelections,
            $capabilityModes,
            $this->lockedSelections,
            $this->disabledEngines,
            $this->customProfile,
        );
    }

    /**
     * @param array<string, string> $lockedSelections
     */
    public function withLockedSelections(array $lockedSelections): self
    {
        return new self(
            $this->profile,
            $this->selectionMode,
            $this->manualSelections,
            $this->capabilityModes,
            $lockedSelections,
            $this->disabledEngines,
            $this->customProfile,
        );
    }

    /**
     * @param list<string> $disabledEngines
     */
    public function withDisabledEngines(array $disabledEngines): self
    {
        return new self(
            $this->profile,
            $this->selectionMode,
            $this->manualSelections,
            $this->capabilityModes,
            $this->lockedSelections,
            $disabledEngines,
            $this->customProfile,
        );
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, string>
     */
    private static function stringMap(array $input): array
    {
        $map = [];

        foreach ($input as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $map[$key] = $value;
            }
        }

        return $map;
    }
}
