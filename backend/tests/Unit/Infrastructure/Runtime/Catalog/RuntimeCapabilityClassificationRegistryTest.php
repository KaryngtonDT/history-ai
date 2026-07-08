<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Runtime\Catalog;

use App\Domain\Runtime\RuntimeCapability;
use App\Domain\Runtime\RuntimeCapabilityClassification;
use App\Infrastructure\Runtime\Catalog\RuntimeCapabilityClassificationRegistry;
use PHPUnit\Framework\TestCase;

final class RuntimeCapabilityClassificationRegistryTest extends TestCase
{
    public function testCoreCapabilitiesAreRequired(): void
    {
        foreach (
            [
                RuntimeCapability::SpeechToText,
                RuntimeCapability::Translation,
                RuntimeCapability::TextToSpeech,
                RuntimeCapability::VoiceClone,
                RuntimeCapability::VideoRender,
            ] as $capability
        ) {
            $meta = RuntimeCapabilityClassificationRegistry::for($capability);
            self::assertSame(RuntimeCapabilityClassification::Core, $meta->classification);
            self::assertTrue($meta->required);
        }
    }

    public function testLipSyncIsPremium(): void
    {
        $meta = RuntimeCapabilityClassificationRegistry::for(RuntimeCapability::LipSync);
        self::assertSame(RuntimeCapabilityClassification::Premium, $meta->classification);
        self::assertFalse($meta->required);
    }

    public function testOptionalCapabilitiesAreNotRequired(): void
    {
        foreach (
            [
                RuntimeCapability::Ocr,
                RuntimeCapability::Vision,
                RuntimeCapability::Embeddings,
                RuntimeCapability::Reranking,
            ] as $capability
        ) {
            $meta = RuntimeCapabilityClassificationRegistry::for($capability);
            self::assertSame(RuntimeCapabilityClassification::Optional, $meta->classification);
            self::assertFalse($meta->required);
            self::assertFalse($meta->enabledByDefault);
        }
    }
}
