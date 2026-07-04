<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser;

use App\Domain\ShadowBrowser\BrowserActivity;
use App\Domain\ShadowBrowser\BrowserPlatform;
use App\Domain\ShadowBrowser\BrowserWorkspace;

final class BrowserAuditLog
{
    public function __construct(
        private readonly BrowserPermissionEvaluator $permissionEvaluator,
        private readonly PlatformDetectionEngine $platformDetectionEngine,
    ) {
    }

    public function recordConnect(BrowserWorkspace $workspace): BrowserActivity
    {
        return BrowserActivity::create(
            'Browser connected',
            BrowserPlatform::Unknown,
            'user_invoked',
            'Connected Shadow browser companion.',
            [],
        );
    }

    public function recordDisconnect(BrowserWorkspace $workspace): BrowserActivity
    {
        return BrowserActivity::create(
            'Browser disconnected',
            BrowserPlatform::Unknown,
            'user_invoked',
            'Disconnected Shadow browser companion.',
            [],
        );
    }

    public function recordContextUpdate(BrowserWorkspace $workspace, string $url, BrowserPlatform $platform): BrowserActivity
    {
        $host = $this->platformDetectionEngine->extractHost($url);
        $permissionsUsed = '' !== $host
            ? $this->permissionEvaluator->grantedPermissions($workspace, $host)
            : [];

        return BrowserActivity::create(
            'Context updated',
            $platform,
            'context_update',
            sprintf('Browser context updated for %s.', $platform->value),
            $permissionsUsed,
            $url,
        );
    }

    public function recordPlatformDetection(BrowserWorkspace $workspace, string $url, BrowserPlatform $platform): BrowserActivity
    {
        $host = $this->platformDetectionEngine->extractHost($url);
        $permissionsUsed = '' !== $host
            ? $this->permissionEvaluator->grantedPermissions($workspace, $host)
            : [];

        return BrowserActivity::create(
            'Platform detected',
            $platform,
            'platform_detection',
            sprintf('Detected platform %s for current URL.', $platform->value),
            $permissionsUsed,
            $url,
        );
    }

    public function recordAction(
        BrowserWorkspace $workspace,
        \App\Domain\ShadowBrowser\BrowserActionType $action,
        string $url,
        BrowserPlatform $platform,
    ): BrowserActivity {
        $host = $this->platformDetectionEngine->extractHost($url);
        $permissionsUsed = '' !== $host
            ? $this->permissionEvaluator->grantedPermissions($workspace, $host)
            : [];

        return BrowserActivity::create(
            ucfirst(str_replace('_', ' ', $action->value)),
            $platform,
            'browser_action',
            sprintf('Shadow browser action: %s.', $action->value),
            $permissionsUsed,
            $url,
        );
    }
}
