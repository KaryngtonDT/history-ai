<?php

declare(strict_types=1);

namespace App\Domain\ShadowPresence;

final readonly class PresencePermissionCollection
{
    /** @param list<PresencePermission> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<PresencePermission> */
    public function all(): array
    {
        return $this->items;
    }

    public function isGranted(PresenceCapability $capability): bool
    {
        foreach ($this->items as $item) {
            if ($item->capability() === $capability) {
                return $item->granted();
            }
        }

        return false;
    }

    public function upsert(PresencePermission $permission): self
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item->capability() !== $permission->capability()) {
                $items[] = $item;
            }
        }

        $items[] = $permission;

        return new self($items);
    }

    /** @return list<string> */
    public function grantedCapabilityValues(): array
    {
        $values = [];

        foreach ($this->items as $item) {
            if ($item->granted()) {
                $values[] = $item->capability()->value;
            }
        }

        return $values;
    }
}
