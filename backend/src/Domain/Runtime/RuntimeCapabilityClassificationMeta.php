<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

final readonly class RuntimeCapabilityClassificationMeta
{
    public function __construct(
        public RuntimeCapability $capability,
        public RuntimeCapabilityClassification $classification,
        public bool $required,
        public bool $enabledByDefault,
        public bool $hardwareDependent,
        public bool $installable,
        public bool $recommended,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'capability' => $this->capability->value,
            'label' => $this->capability->label(),
            'classification' => $this->classification->value,
            'classificationLabel' => $this->classification->label(),
            'required' => $this->required,
            'enabledByDefault' => $this->enabledByDefault,
            'hardwareDependent' => $this->hardwareDependent,
            'installable' => $this->installable,
            'recommended' => $this->recommended,
        ];
    }
}
