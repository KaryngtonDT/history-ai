<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ShadowBrowser;

use App\Domain\ShadowBrowser\BrowserState;
use App\Domain\ShadowBrowser\BrowserWorkspace;
use PHPUnit\Framework\TestCase;

final class BrowserWorkspaceTest extends TestCase
{
    public function testDisconnectClearsActiveSessionId(): void
    {
        $workspace = BrowserWorkspace::create(scopeKey: 'unit-browser');
        $workspace = $workspace->connect();

        self::assertNotNull($workspace->activeSession());
        self::assertSame(BrowserState::Connected, $workspace->activeSession()?->state());

        $workspace = $workspace->disconnect();

        self::assertNull($workspace->activeSessionId());
        self::assertNull($workspace->activeSession());
        self::assertNull($workspace->currentContext());
    }
}
