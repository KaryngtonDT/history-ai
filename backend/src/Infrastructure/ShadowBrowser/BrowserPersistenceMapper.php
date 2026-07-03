<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowBrowser;

use App\Domain\ShadowBrowser\BrowserActivity;
use App\Domain\ShadowBrowser\BrowserActivityCollection;
use App\Domain\ShadowBrowser\BrowserContext;
use App\Domain\ShadowBrowser\BrowserPlatform;
use App\Domain\ShadowBrowser\BrowserSession;
use App\Domain\ShadowBrowser\BrowserSessionCollection;
use App\Domain\ShadowBrowser\BrowserSitePolicy;
use App\Domain\ShadowBrowser\BrowserSitePolicyCollection;
use App\Domain\ShadowBrowser\BrowserState;
use App\Domain\ShadowBrowser\BrowserTab;
use App\Domain\ShadowBrowser\BrowserWorkspace;
use App\Domain\ShadowBrowser\BrowserWorkspaceId;
use App\Domain\ShadowBrowser\Exception\InvalidShadowBrowserException;
use JsonException;

final class BrowserPersistenceMapper
{
    /** @return array<string, mixed> */
    public function toArray(BrowserWorkspace $workspace): array
    {
        return [
            'id' => $workspace->id()->value,
            'scopeKey' => $workspace->scopeKey(),
            'activeSessionId' => $workspace->activeSessionId(),
            'sessions' => array_map($this->sessionToArray(...), $workspace->sessions()->all()),
            'activities' => array_map($this->activityToArray(...), $workspace->activities()->all()),
            'sitePolicies' => array_map($this->sitePolicyToArray(...), $workspace->sitePolicies()->all()),
            'currentContext' => null !== $workspace->currentContext()
                ? $this->contextToArray($workspace->currentContext())
                : null,
        ];
    }

    public function fromJson(string $json): BrowserWorkspace
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidShadowBrowserException('Stored browser workspace is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded) || !is_string($decoded['id'] ?? null)) {
            throw new InvalidShadowBrowserException('Stored browser workspace is invalid.');
        }

        return new BrowserWorkspace(
            new BrowserWorkspaceId($decoded['id']),
            is_string($decoded['scopeKey'] ?? null) ? $decoded['scopeKey'] : 'default',
            $this->sessionsFromArray(is_array($decoded['sessions'] ?? null) ? $decoded['sessions'] : []),
            $this->activitiesFromArray(is_array($decoded['activities'] ?? null) ? $decoded['activities'] : []),
            $this->sitePoliciesFromArray(is_array($decoded['sitePolicies'] ?? null) ? $decoded['sitePolicies'] : []),
            is_string($decoded['activeSessionId'] ?? null) ? $decoded['activeSessionId'] : null,
            $this->contextFromArray(is_array($decoded['currentContext'] ?? null) ? $decoded['currentContext'] : null),
        );
    }

    /** @return array<string, mixed> */
    private function sessionToArray(BrowserSession $session): array
    {
        return [
            'id' => $session->id(),
            'scopeKey' => $session->scopeKey(),
            'state' => $session->state()->value,
            'shadowSessionId' => $session->shadowSessionId(),
            'activeTab' => null !== $session->activeTab() ? $this->tabToArray($session->activeTab()) : null,
            'connectedAt' => $session->connectedAt()->format(\DateTimeInterface::ATOM),
            'lastActiveAt' => $session->lastActiveAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    /** @return array<string, mixed> */
    private function tabToArray(BrowserTab $tab): array
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

    /** @return array<string, mixed> */
    private function sitePolicyToArray(BrowserSitePolicy $policy): array
    {
        return [
            'host' => $policy->host(),
            'allowed' => $policy->allowed(),
            'permissions' => $policy->permissions(),
        ];
    }

    /** @return array<string, mixed> */
    private function contextToArray(BrowserContext $context): array
    {
        return [
            'scopeKey' => $context->scopeKey(),
            'url' => $context->url(),
            'title' => $context->title(),
            'tabId' => $context->tabId(),
            'platform' => $context->platform()->value,
            'selection' => $context->selection(),
            'shadowSessionId' => $context->shadowSessionId(),
            'conversationSessionId' => $context->conversationSessionId(),
        ];
    }

    /** @param list<array<string, mixed>> $items */
    private function sessionsFromArray(array $items): BrowserSessionCollection
    {
        $sessions = [];

        foreach ($items as $item) {
            if (!is_string($item['id'] ?? null)) {
                continue;
            }

            $state = is_string($item['state'] ?? null)
                ? BrowserState::tryFrom($item['state']) ?? BrowserState::Disconnected
                : BrowserState::Disconnected;

            $activeTab = null;

            if (is_array($item['activeTab'] ?? null)) {
                $activeTab = $this->tabFromArray($item['activeTab']);
            }

            $sessions[] = new BrowserSession(
                $item['id'],
                is_string($item['scopeKey'] ?? null) ? $item['scopeKey'] : 'default',
                $state,
                is_string($item['shadowSessionId'] ?? null) ? $item['shadowSessionId'] : null,
                $activeTab,
                new \DateTimeImmutable(is_string($item['connectedAt'] ?? null) ? $item['connectedAt'] : 'now'),
                new \DateTimeImmutable(is_string($item['lastActiveAt'] ?? null) ? $item['lastActiveAt'] : 'now'),
            );
        }

        return new BrowserSessionCollection($sessions);
    }

    /** @param array<string, mixed> $data */
    private function tabFromArray(array $data): ?BrowserTab
    {
        if (!is_string($data['tabId'] ?? null) || !is_string($data['url'] ?? null)) {
            return null;
        }

        $platform = is_string($data['platform'] ?? null)
            ? BrowserPlatform::tryFrom($data['platform']) ?? BrowserPlatform::Unknown
            : BrowserPlatform::Unknown;

        return BrowserTab::create(
            $data['tabId'],
            $data['url'],
            is_string($data['title'] ?? null) ? $data['title'] : '',
            $platform,
            is_string($data['selection'] ?? null) ? $data['selection'] : null,
        );
    }

    /** @param list<array<string, mixed>> $items */
    private function activitiesFromArray(array $items): BrowserActivityCollection
    {
        $activities = [];

        foreach ($items as $item) {
            if (!is_string($item['id'] ?? null) || !is_string($item['label'] ?? null)) {
                continue;
            }

            $platform = is_string($item['platform'] ?? null)
                ? BrowserPlatform::tryFrom($item['platform']) ?? BrowserPlatform::Unknown
                : BrowserPlatform::Unknown;

            $activities[] = new BrowserActivity(
                $item['id'],
                $item['label'],
                $platform,
                is_string($item['reason'] ?? null) ? $item['reason'] : 'unknown',
                is_string($item['detail'] ?? null) ? $item['detail'] : '',
                new \DateTimeImmutable(is_string($item['recordedAt'] ?? null) ? $item['recordedAt'] : 'now'),
                is_array($item['permissionsUsed'] ?? null)
                    ? array_values(array_filter($item['permissionsUsed'], 'is_string'))
                    : [],
                is_string($item['url'] ?? null) ? $item['url'] : null,
            );
        }

        return new BrowserActivityCollection($activities);
    }

    /** @param list<array<string, mixed>> $items */
    private function sitePoliciesFromArray(array $items): BrowserSitePolicyCollection
    {
        $policies = [];

        foreach ($items as $item) {
            if (!is_string($item['host'] ?? null)) {
                continue;
            }

            $permissions = [];

            if (is_array($item['permissions'] ?? null)) {
                foreach ($item['permissions'] as $key => $granted) {
                    if (is_string($key)) {
                        $permissions[$key] = (bool) $granted;
                    }
                }
            }

            if ([] === $permissions) {
                $policies[] = BrowserSitePolicy::create($item['host'], (bool) ($item['allowed'] ?? true));
                continue;
            }

            $base = BrowserSitePolicy::create($item['host'], (bool) ($item['allowed'] ?? true));
            $policies[] = $base->withUpdates(permissions: $permissions);
        }

        return new BrowserSitePolicyCollection($policies);
    }

    /** @param array<string, mixed>|null $data */
    private function contextFromArray(?array $data): ?BrowserContext
    {
        if (null === $data || !is_string($data['tabId'] ?? null) || !is_string($data['url'] ?? null)) {
            return null;
        }

        $platform = is_string($data['platform'] ?? null)
            ? BrowserPlatform::tryFrom($data['platform']) ?? BrowserPlatform::Unknown
            : BrowserPlatform::Unknown;

        $tab = BrowserTab::create(
            $data['tabId'],
            $data['url'],
            is_string($data['title'] ?? null) ? $data['title'] : '',
            $platform,
            is_string($data['selection'] ?? null) ? $data['selection'] : null,
        );

        return BrowserContext::fromTab(
            is_string($data['scopeKey'] ?? null) ? $data['scopeKey'] : 'default',
            $tab,
            is_string($data['shadowSessionId'] ?? null) ? $data['shadowSessionId'] : null,
            is_string($data['conversationSessionId'] ?? null) ? $data['conversationSessionId'] : null,
        );
    }
}
