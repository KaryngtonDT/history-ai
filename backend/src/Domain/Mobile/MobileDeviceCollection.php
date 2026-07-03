<?php

declare(strict_types=1);

namespace App\Domain\Mobile;

final readonly class MobileDeviceCollection
{
    /** @param list<MobileDevice> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<MobileDevice> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $deviceId): ?MobileDevice
    {
        foreach ($this->items as $item) {
            if ($item->deviceId() === $deviceId) {
                return $item;
            }
        }

        return null;
    }

    public function upsert(MobileDevice $device): self
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item->deviceId() !== $device->deviceId()) {
                $items[] = $item;
            }
        }

        $items[] = $device;

        return new self($items);
    }
}
