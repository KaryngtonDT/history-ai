<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser;

use App\Domain\ShadowBrowser\BrowserActivity;
use App\Domain\ShadowBrowser\BrowserWorkspace;

final class BrowserHistoryBuilder
{
    /** @return list<array<string, mixed>> */
    public function build(BrowserWorkspace $workspace, ?int $limit = null): array
    {
        $activities = $workspace->activities()->all();

        if (null !== $limit && $limit > 0) {
            $activities = array_slice($activities, -$limit);
        }

        return array_map(
            static fn (BrowserActivity $activity): array => [
                'id' => $activity->id(),
                'label' => $activity->label(),
                'platform' => $activity->platform()->value,
                'reason' => $activity->reason(),
                'detail' => $activity->detail(),
                'recordedAt' => $activity->recordedAt()->format(\DateTimeInterface::ATOM),
                'permissionsUsed' => $activity->permissionsUsed(),
                'url' => $activity->url(),
            ],
            $activities,
        );
    }
}
