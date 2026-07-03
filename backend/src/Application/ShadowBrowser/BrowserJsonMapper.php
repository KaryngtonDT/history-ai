<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser;

use App\Domain\ShadowBrowser\BrowserActivity;
use App\Domain\ShadowBrowser\BrowserContext;
use App\Domain\ShadowBrowser\BrowserPermission;
use App\Domain\ShadowBrowser\BrowserSession;
use App\Domain\ShadowBrowser\BrowserSitePolicy;
use App\Domain\ShadowBrowser\BrowserState;
use App\Domain\ShadowBrowser\BrowserTab;
use App\Domain\ShadowBrowser\BrowserWorkspace;

final class BrowserJsonMapper
{
    /** @return array<string, mixed> */
    public function session(?BrowserSession $session): array
    {
        if (null === $session) {
            return [
                'active' => false,
                'session' => null,
            ];
        }

        return [
            'active' => BrowserState::Connected === $session->state(),
            'session' => [
                'id' => $session->id(),
                'scopeKey' => $session->scopeKey(),
                'state' => $session->state()->value,
                'shadowSessionId' => $session->shadowSessionId(),
                'activeTab' => null !== $session->activeTab() ? $this->tab($session->activeTab()) : null,
                'connectedAt' => $session->connectedAt()->format(\DateTimeInterface::ATOM),
                'lastActiveAt' => $session->lastActiveAt()->format(\DateTimeInterface::ATOM),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function tab(BrowserTab $tab): array
    {
        return [
            'tabId' => $tab->tabId(),
            'url' => $tab->url(),
            'title' => $tab->title(),
            'platform' => $tab->platform()->value,
            'selection' => $tab->selection(),
        ];
    }

    /** @return array<string, mixed> */
    public function context(?BrowserContext $context): array
    {
        if (null === $context) {
            return [
                'available' => false,
                'context' => null,
            ];
        }

        return [
            'available' => true,
            'context' => [
                'scopeKey' => $context->scopeKey(),
                'url' => $context->url(),
                'title' => $context->title(),
                'tabId' => $context->tabId(),
                'platform' => $context->platform()->value,
                'selection' => $context->selection(),
                'shadowSessionId' => $context->shadowSessionId(),
                'conversationSessionId' => $context->conversationSessionId(),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function permissions(BrowserWorkspace $workspace): array
    {
        return [
            'scopeKey' => $workspace->scopeKey(),
            'sitePolicies' => array_map(
                fn (BrowserSitePolicy $policy): array => $this->sitePolicy($policy),
                $workspace->sitePolicies()->all(),
            ),
            'defaults' => $this->sitePolicy(BrowserSitePolicy::create('default')),
        ];
    }

    /** @return array<string, mixed> */
    public function sitePolicy(BrowserSitePolicy $policy): array
    {
        return [
            'host' => $policy->host(),
            'allowed' => $policy->allowed(),
            'permissions' => array_map(
                static fn (BrowserPermission $permission): array => [
                    'permission' => $permission->value,
                    'granted' => $policy->isGranted($permission),
                ],
                BrowserPermission::cases(),
            ),
        ];
    }

    /** @return array<string, mixed> */
    public function history(BrowserWorkspace $workspace): array
    {
        return [
            'scopeKey' => $workspace->scopeKey(),
            'activities' => array_map(
                fn (BrowserActivity $activity): array => $this->activityToArray($activity),
                $workspace->activities()->all(),
            ),
        ];
    }

    /** @return array<string, mixed> */
    public function workspace(BrowserWorkspace $workspace): array
    {
        return [
            'id' => $workspace->id()->value,
            'scopeKey' => $workspace->scopeKey(),
            'session' => $this->session($workspace->activeSession()),
            'context' => $this->context($workspace->currentContext()),
        ];
    }

    /** @return array<string, mixed> */
    public function platform(string $url, string $platform, string $host): array
    {
        return [
            'url' => $url,
            'platform' => $platform,
            'host' => $host,
        ];
    }

    /** @return array<string, mixed> */
    private function activityToArray(BrowserActivity $activity): array
    {
        return [
            'id' => $activity->id(),
            'label' => $activity->label(),
            'platform' => $activity->platform()->value,
            'reason' => $activity->reason(),
            'detail' => $activity->detail(),
            'recordedAt' => $activity->recordedAt()->format(\DateTimeInterface::ATOM),
            'permissionsUsed' => $activity->permissionsUsed(),
            'url' => $activity->url(),
        ];
    }
}
