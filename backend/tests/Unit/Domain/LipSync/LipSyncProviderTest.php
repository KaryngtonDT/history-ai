<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\LipSync;

use App\Domain\LipSync\LipSyncProvider;
use PHPUnit\Framework\TestCase;

final class LipSyncProviderTest extends TestCase
{
    public function testContainsExpectedProviders(): void
    {
        self::assertSame('latentsync', LipSyncProvider::LatentSync->value);
        self::assertSame('wav2lip', LipSyncProvider::Wav2Lip->value);
        self::assertSame('mock', LipSyncProvider::Mock->value);
    }

    public function testCanBeResolvedFromValue(): void
    {
        self::assertSame(
            LipSyncProvider::LatentSync,
            LipSyncProvider::from('latentsync'),
        );
    }
}
