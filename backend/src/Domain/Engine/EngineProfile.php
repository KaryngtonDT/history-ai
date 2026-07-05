<?php

declare(strict_types=1);

namespace App\Domain\Engine;

final readonly class EngineProfile
{
    /**
     * @param list<EngineProfileName> $supportedProfiles
     */
    public function __construct(
        public EngineProfileName $name,
        public string $description,
        public array $supportedProfiles = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name->value,
            'label' => $this->name->label(),
            'description' => $this->description,
            'supportedProfiles' => array_map(
                static fn (EngineProfileName $profile): string => $profile->value,
                $this->supportedProfiles,
            ),
        ];
    }
}
