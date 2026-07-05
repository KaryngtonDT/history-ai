<?php

declare(strict_types=1);

namespace App\Domain\Engine;

enum EngineProfileName: string
{
    case MaximumQuality = 'maximum_quality';
    case Balanced = 'balanced';
    case Fast = 'fast';
    case LowVram = 'low_vram';
    case EnergySaving = 'energy_saving';
    case Offline = 'offline';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::MaximumQuality => 'Maximum Quality',
            self::Balanced => 'Balanced',
            self::Fast => 'Fast',
            self::LowVram => 'Low VRAM',
            self::EnergySaving => 'Energy Saving',
            self::Offline => 'Offline',
            self::Custom => 'Custom',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function catalog(): array
    {
        return array_map(
            static fn (self $profile): array => [
                'value' => $profile->value,
                'label' => $profile->label(),
            ],
            self::cases(),
        );
    }
}
