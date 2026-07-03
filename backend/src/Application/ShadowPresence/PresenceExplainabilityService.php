<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence;

use App\Domain\ShadowPresence\PresenceEvent;

final class PresenceExplainabilityService
{
    /** @return array<string, mixed> */
    public function explain(?PresenceEvent $event): array
    {
        if (null === $event) {
            return [
                'reason' => 'none',
                'detail' => 'No presence activity recorded yet.',
                'surface' => null,
                'permissionsUsed' => [],
                'humanReadable' => 'Shadow has not recorded any presence activity for this scope.',
            ];
        }

        return [
            'reason' => $event->reason(),
            'detail' => $event->detail(),
            'surface' => $event->surface()->value,
            'permissionsUsed' => $event->permissionsUsed(),
            'humanReadable' => $this->humanReadable($event),
        ];
    }

    private function humanReadable(PresenceEvent $event): string
    {
        return match ($event->reason()) {
            'user_invoked' => sprintf('You invoked Shadow on %s.', $event->surface()->value),
            'context_hub' => 'Shadow assembled context from your existing learning profile.',
            'mission_scheduled' => 'A scheduled mission triggered this presence event.',
            'recommendation_authorized' => 'You authorized this recommendation.',
            default => $event->detail() !== '' ? $event->detail() : $event->label(),
        };
    }
}
