<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence;

use App\Domain\ShadowPresence\PresenceEvent;
use App\Domain\ShadowPresence\PresenceWorkspace;

final class PresenceHistoryBuilder
{
    /** @return list<array<string, mixed>> */
    public function build(PresenceWorkspace $workspace, ?int $limit = null): array
    {
        $events = $workspace->events()->all();

        if (null !== $limit && $limit > 0) {
            $events = array_slice($events, -$limit);
        }

        return array_map(
            static fn (PresenceEvent $event): array => [
                'id' => $event->id(),
                'label' => $event->label(),
                'surface' => $event->surface()->value,
                'reason' => $event->reason(),
                'detail' => $event->detail(),
                'recordedAt' => $event->recordedAt()->format(\DateTimeInterface::ATOM),
                'permissionsUsed' => $event->permissionsUsed(),
            ],
            $events,
        );
    }
}
