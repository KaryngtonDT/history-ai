<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Mobile;

use App\Domain\Mobile\MobileConnectionMode;
use App\Domain\Mobile\MobileState;
use App\Domain\Mobile\MobileWorkspace;
use PHPUnit\Framework\TestCase;

final class MobileWorkspaceTest extends TestCase
{
    public function testRegisterDeviceAddsDeviceAndSetsActiveDeviceId(): void
    {
        $workspace = MobileWorkspace::create(scopeKey: 'unit-mobile');

        $workspace = $workspace->registerDevice('device-1', 'android', 'Pixel Test');

        self::assertSame('device-1', $workspace->activeDeviceId());
        self::assertNotNull($workspace->devices()->find('device-1'));
        self::assertSame('android', $workspace->devices()->find('device-1')?->platform());
    }

    public function testUpdateConnectionChangesModeAndUrls(): void
    {
        $workspace = MobileWorkspace::create(scopeKey: 'unit-mobile');

        $workspace = $workspace->updateConnection([
            'mode' => 'tailscale',
            'tailscaleUrl' => 'http://home.tailnet:8080',
            'homeWifiSsids' => ['HomeWiFi'],
        ]);

        self::assertSame(MobileConnectionMode::Tailscale, $workspace->connection()->mode());
        self::assertSame('http://home.tailnet:8080', $workspace->connection()->tailscaleUrl());
        self::assertSame(['HomeWiFi'], $workspace->connection()->homeWifiSsids());
    }

    public function testConnectDeviceAndSyncTouchActiveSession(): void
    {
        $workspace = MobileWorkspace::create(scopeKey: 'unit-mobile');
        $workspace = $workspace->registerDevice('device-1', 'ios', 'iPhone Test');
        $workspace = $workspace->connectDevice('device-1');

        self::assertNotNull($workspace->activeSession());
        self::assertSame(MobileState::Connected, $workspace->activeSession()?->state());

        $before = $workspace->activeSession()?->lastActiveAt();
        usleep(1000);
        $workspace = $workspace->sync();

        self::assertNotNull($before);
        self::assertGreaterThanOrEqual(
            $before->getTimestamp(),
            $workspace->activeSession()?->lastActiveAt()->getTimestamp(),
        );
    }
}
