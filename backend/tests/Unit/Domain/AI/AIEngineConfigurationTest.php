<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\AI;

use App\Domain\AI\AIEngine;
use App\Domain\AI\AIEngineCapability;
use App\Domain\AI\AIEngineConfiguration;
use App\Domain\AI\AIEngineId;
use App\Domain\AI\AIEngineProvider;
use App\Domain\AI\Exception\InvalidAIEngineException;
use PHPUnit\Framework\TestCase;

final class AIEngineConfigurationTest extends TestCase
{
    public function testEmptyConfigurationHasNoDefaults(): void
    {
        $configuration = AIEngineConfiguration::empty();

        self::assertNull($configuration->defaultProviderFor(AIEngineCapability::SpeechToText));
        self::assertSame([], $configuration->allDefaults());
    }

    public function testWithDefaultProviderStoresCapabilityDefault(): void
    {
        $configuration = AIEngineConfiguration::empty()
            ->withDefaultProvider(AIEngineCapability::SpeechToText, 'faster_whisper');

        self::assertSame('faster_whisper', $configuration->defaultProviderFor(AIEngineCapability::SpeechToText));
    }

    public function testRejectsUnknownCapabilityKey(): void
    {
        $this->expectException(InvalidAIEngineException::class);

        new AIEngineConfiguration(['unknown' => 'provider']);
    }
}
