<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Runtime\Catalog;

use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Runtime\CapabilityMaturityLevel;
use App\Infrastructure\Runtime\Catalog\CapabilityMaturityRegistry;
use App\Infrastructure\Runtime\Catalog\EngineCatalogDefinitions;
use PHPUnit\Framework\TestCase;

final class CapabilityMaturityRegistryTest extends TestCase
{
    public function testCatalogHasThirtyThreeEngines(): void
    {
        self::assertCount(33, EngineCatalogDefinitions::all());
    }

    public function testMaturityCoversAllCapabilities(): void
    {
        $entries = CapabilityMaturityRegistry::all();

        self::assertCount(count(EngineCatalogCapability::cases()), $entries);

        $capabilities = array_column($entries, 'capability');
        foreach (EngineCatalogCapability::cases() as $case) {
            self::assertContains($case->value, $capabilities);
        }
    }

	public function testLipSyncIncludesLegacyMuseTalk(): void
	{
		$lipSync = null;
		foreach (CapabilityMaturityRegistry::all() as $entry) {
			if ('lip_sync' === $entry['capability']) {
				$lipSync = $entry;
				break;
			}
		}

		self::assertNotNull($lipSync);
		self::assertSame(CapabilityMaturityLevel::Beta->value, $lipSync['maturity']);

		$ids = array_column($lipSync['engines'], 'id');
		self::assertContains('musetalk', $ids);
		self::assertContains('liveportrait', $ids);
		self::assertContains('wav2lip', $ids);
	}

    public function testSpeechToTextIncludesWhisperCpp(): void
    {
        foreach (CapabilityMaturityRegistry::all() as $entry) {
            if ('speech_to_text' !== $entry['capability']) {
                continue;
            }

            $ids = array_column($entry['engines'], 'id');
            self::assertContains('whisper_cpp', $ids);
            self::assertSame('faster_whisper_large_v3', $entry['defaultEngineId']);

            return;
        }

        self::fail('speech_to_text capability not found');
    }
}
