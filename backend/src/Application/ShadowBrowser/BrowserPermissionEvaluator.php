<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser;

use App\Domain\ShadowBrowser\BrowserPermission;
use App\Domain\ShadowBrowser\BrowserSitePolicy;
use App\Domain\ShadowBrowser\BrowserWorkspace;

final class BrowserPermissionEvaluator
{
    public function isGranted(BrowserWorkspace $workspace, string $host, BrowserPermission $permission): bool
    {
        $policy = $workspace->sitePolicies()->findByHost($host);

        if (null !== $policy) {
            return $policy->isGranted($permission);
        }

        return $this->defaultPolicy($host)->isGranted($permission);
    }

    /** @return list<string> */
    public function grantedPermissions(BrowserWorkspace $workspace, string $host): array
    {
        $values = [];

        foreach (BrowserPermission::cases() as $permission) {
            if ($this->isGranted($workspace, $host, $permission)) {
                $values[] = $permission->value;
            }
        }

        return $values;
    }

    public function defaultPolicy(string $host): BrowserSitePolicy
    {
        return BrowserSitePolicy::create($host);
    }

    /** @param list<array<string, mixed>> $updates */
    public function applySitePolicyUpdates(BrowserWorkspace $workspace, array $updates): BrowserWorkspace
    {
        $policies = $workspace->sitePolicies();

        foreach ($updates as $update) {
            if (!is_string($update['host'] ?? null)) {
                continue;
            }

            $host = strtolower(trim($update['host']));
            $existing = $policies->findByHost($host) ?? BrowserSitePolicy::create($host);
            $permissionUpdates = [];

            if (is_array($update['permissions'] ?? null)) {
                foreach ($update['permissions'] as $item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    $permissionValue = is_string($item['permission'] ?? null)
                        ? $item['permission']
                        : (is_string($item['capability'] ?? null) ? $item['capability'] : null);

                    if (null === $permissionValue) {
                        continue;
                    }

                    $permission = BrowserPermission::tryFrom($permissionValue);

                    if (null !== $permission) {
                        $permissionUpdates[$permission->value] = (bool) ($item['granted'] ?? false);
                    }
                }
            }

            $policies = $policies->upsert(
                $existing->withUpdates(
                    allowed: is_bool($update['allowed'] ?? null) ? $update['allowed'] : null,
                    permissions: [] !== $permissionUpdates ? $permissionUpdates : null,
                ),
            );
        }

        return $workspace->updateSitePolicies($policies);
    }
}
