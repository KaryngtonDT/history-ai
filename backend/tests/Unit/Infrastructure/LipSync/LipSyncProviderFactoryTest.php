<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\LipSync;

use App\Domain\LipSync\LipSyncProvider;
use App\Domain\LipSync\LipSyncProviderInterface;
use App\Infrastructure\LipSync\Exception\InvalidLipSyncConfigurationException;
use App\Infrastructure\LipSync\FixedLatentSyncProcessRunner;
use App\Infrastructure\LipSync\LatentSyncProvider;
use App\Infrastructure\LipSync\LipSyncMapper;
use App\Infrastructure\LipSync\LipSyncProviderFactory;
use App\Infrastructure\LipSync\MockLipSyncProvider;
use PHPUnit\Framework\TestCase;

final class LipSyncProviderFactoryTest extends TestCase
{
    private LipSyncProviderFactory $factory;

    protected function setUp(): void
    {
        $outputDirectory = sys_get_temp_dir().'/history-ai-lipsync-factory';

        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory);
        }

        $this->factory = new LipSyncProviderFactory(
            'latentsync',
            new LatentSyncProvider(
                new FixedLatentSyncProcessRunner(),
                new LipSyncMapper(),
                'latentsync',
                'latentsync',
                '/models/latentsync',
                $outputDirectory,
            ),
            new MockLipSyncProvider(),
        );
    }

    public function testResolveDefaultReturnsLatentSync(): void
    {
        self::assertInstanceOf(LatentSyncProvider::class, $this->factory->resolve());
    }

    public function testResolveExplicitLatentSync(): void
    {
        self::assertInstanceOf(
            LatentSyncProvider::class,
            $this->factory->resolve(LipSyncProvider::LatentSync),
        );
    }

    public function testResolveMock(): void
    {
        self::assertInstanceOf(
            MockLipSyncProvider::class,
            $this->factory->resolve(LipSyncProvider::Mock),
        );
    }

    public function testUnimplementedProviderThrows(): void
    {
        $this->expectException(InvalidLipSyncConfigurationException::class);

        $this->factory->resolve(LipSyncProvider::Wav2Lip);
    }

    public function testUnknownDefaultProviderThrows(): void
    {
        $outputDirectory = sys_get_temp_dir().'/history-ai-lipsync-factory-unknown';

        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory);
        }

        $factory = new LipSyncProviderFactory(
            'unknown',
            new LatentSyncProvider(
                new FixedLatentSyncProcessRunner(),
                new LipSyncMapper(),
                'latentsync',
                'latentsync',
                '/models/latentsync',
                $outputDirectory,
            ),
            new MockLipSyncProvider(),
        );

        $this->expectException(InvalidLipSyncConfigurationException::class);

        $factory->resolve();
    }

    public function testResolvedProviderImplementsInterface(): void
    {
        self::assertInstanceOf(LipSyncProviderInterface::class, $this->factory->resolve());
    }
}
