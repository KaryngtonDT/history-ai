<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\VoiceClone;

use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Domain\VoiceClone\VoiceCloneProviderInterface;
use App\Infrastructure\VoiceClone\Exception\InvalidVoiceCloneConfigurationException;
use App\Infrastructure\VoiceClone\FixedOpenVoiceProcessRunner;
use App\Infrastructure\VoiceClone\MockVoiceCloneProvider;
use App\Infrastructure\VoiceClone\OpenVoiceProvider;
use App\Infrastructure\VoiceClone\VoiceCloneMapper;
use App\Infrastructure\VoiceClone\VoiceCloneProcessingContext;
use App\Infrastructure\VoiceClone\VoiceCloneProviderFactory;
use PHPUnit\Framework\TestCase;

final class VoiceCloneProviderFactoryTest extends TestCase
{
    private VoiceCloneProviderFactory $factory;

    protected function setUp(): void
    {
        $outputDirectory = sys_get_temp_dir().'/history-ai-voice-clone-factory';

        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory);
        }

        $this->factory = new VoiceCloneProviderFactory(
            'openvoice',
            new OpenVoiceProvider(
                new FixedOpenVoiceProcessRunner(),
                new VoiceCloneMapper(),
                new VoiceCloneProcessingContext(),
                'openvoice',
                'openvoice_v2',
                '/models/openvoice',
                $outputDirectory,
            ),
            new MockVoiceCloneProvider(),
        );
    }

    public function testResolveDefaultReturnsOpenVoice(): void
    {
        self::assertInstanceOf(OpenVoiceProvider::class, $this->factory->resolve());
    }

    public function testResolveExplicitOpenVoice(): void
    {
        self::assertInstanceOf(
            OpenVoiceProvider::class,
            $this->factory->resolve(VoiceCloneProvider::OpenVoice),
        );
    }

    public function testResolveMock(): void
    {
        self::assertInstanceOf(
            MockVoiceCloneProvider::class,
            $this->factory->resolve(VoiceCloneProvider::Mock),
        );
    }

    public function testUnimplementedProviderThrows(): void
    {
        $this->expectException(InvalidVoiceCloneConfigurationException::class);

        $this->factory->resolve(VoiceCloneProvider::SeedVC);
    }

    public function testUnknownDefaultProviderThrows(): void
    {
        $outputDirectory = sys_get_temp_dir().'/history-ai-voice-clone-factory-unknown';

        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory);
        }

        $factory = new VoiceCloneProviderFactory(
            'unknown',
            new OpenVoiceProvider(
                new FixedOpenVoiceProcessRunner(),
                new VoiceCloneMapper(),
                new VoiceCloneProcessingContext(),
                'openvoice',
                'openvoice_v2',
                '/models/openvoice',
                $outputDirectory,
            ),
            new MockVoiceCloneProvider(),
        );

        $this->expectException(InvalidVoiceCloneConfigurationException::class);

        $factory->resolve();
    }

    public function testResolvedProviderImplementsInterface(): void
    {
        self::assertInstanceOf(VoiceCloneProviderInterface::class, $this->factory->resolve());
    }
}
