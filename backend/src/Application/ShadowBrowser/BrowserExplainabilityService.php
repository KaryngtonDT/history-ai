<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser;

use App\Domain\ShadowBrowser\BrowserActivity;

final class BrowserExplainabilityService
{
    /** @return array<string, mixed> */
    public function explain(?BrowserActivity $activity): array
    {
        if (null === $activity) {
            return [
                'reason' => 'none',
                'detail' => 'No browser activity recorded yet.',
                'platform' => null,
                'permissionsUsed' => [],
                'humanReadable' => 'Shadow has not recorded any browser activity for this scope.',
            ];
        }

        return [
            'reason' => $activity->reason(),
            'detail' => $activity->detail(),
            'platform' => $activity->platform()->value,
            'permissionsUsed' => $activity->permissionsUsed(),
            'url' => $activity->url(),
            'humanReadable' => $this->humanReadable($activity),
        ];
    }

    private function humanReadable(BrowserActivity $activity): string
    {
        return match ($activity->reason()) {
            'user_invoked' => 'You connected or disconnected the Shadow browser companion.',
            'context_update' => sprintf(
                'Shadow updated browser context on %s.',
                $activity->platform()->value,
            ),
            'platform_detection' => sprintf(
                'Shadow detected platform %s for the current page.',
                $activity->platform()->value,
            ),
            default => $activity->detail() !== '' ? $activity->detail() : $activity->label(),
        };
    }
}
