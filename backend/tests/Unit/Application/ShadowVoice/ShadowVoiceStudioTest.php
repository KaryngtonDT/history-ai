<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowVoice;

use App\Application\ShadowVoice\ShadowVoiceCatalog;
use App\Application\ShadowVoice\ShadowVoiceCollection;
use App\Application\ShadowVoice\ShadowVoicePreset;
use App\Application\ShadowVoice\ShadowVoicePresetMapper;
use App\Application\ShadowVoice\ShadowVoiceStudio;
use App\Domain\ShadowIdentity\ShadowVoicePersona;
use PHPUnit\Framework\TestCase;

final class ShadowVoiceCatalogTest extends TestCase
{
    public function testCatalogIncludesMultipleCollections(): void
    {
        $voices = ShadowVoiceCatalog::all();

        self::assertNotEmpty($voices);
        self::assertNotNull(ShadowVoiceCatalog::findById('browser-default'));
        self::assertNotEmpty(ShadowVoiceCatalog::forCollection(ShadowVoiceCollection::GreatStorytellers));
    }

    public function testEnginesMarkBrowserTtsAvailable(): void
    {
        $engines = ShadowVoiceCatalog::engines();
        $browser = array_values(array_filter($engines, static fn (array $e): bool => 'browser_tts' === $e['id']))[0];

        self::assertTrue($browser['available']);
    }
}

final class ShadowVoiceStudioTest extends TestCase
{
    private ShadowVoiceStudio $studio;

    protected function setUp(): void
    {
        $this->studio = new ShadowVoiceStudio(new ShadowVoicePresetMapper());
    }

    public function testPreviewReturnsVoiceText(): void
    {
        $preview = $this->studio->preview('storyteller-warm-en');

        self::assertSame('storyteller-warm-en', $preview['voiceId']);
        self::assertStringContainsString('story', strtolower((string) $preview['text']));
    }

    public function testStorytellerPresetMapsPersona(): void
    {
        $result = $this->studio->applyPreset(ShadowVoicePreset::Storyteller);

        self::assertSame(ShadowVoicePersona::Storyteller->value, $result['persona']);
        self::assertSame('storyteller-warm-en', $result['voiceProfile']['voiceId']);
    }
}
