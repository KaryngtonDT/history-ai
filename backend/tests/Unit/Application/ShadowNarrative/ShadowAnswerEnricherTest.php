<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowNarrative;

use App\Application\ShadowIdentity\ShadowLanguageComposer;
use App\Application\ShadowNarrative\ShadowAnswerEnricher;
use App\Application\ShadowNarrative\ShadowNarrationDecorator;
use App\Application\ShadowNarrative\ShadowPersonaSuggestionEngine;
use App\Application\ShadowNarrative\ShadowSpeechDecorator;
use App\Application\ShadowNarrative\ShadowStorytellingDecorator;
use App\Domain\ShadowIdentity\ShadowIdentityPreferences;
use App\Domain\ShadowIdentity\ShadowNarrationStyle;
use App\Domain\ShadowIdentity\ShadowVoicePersona;
use PHPUnit\Framework\TestCase;

final class ShadowAnswerEnricherTest extends TestCase
{
    private ShadowAnswerEnricher $enricher;

    protected function setUp(): void
    {
        $this->enricher = new ShadowAnswerEnricher(
            new ShadowStorytellingDecorator(),
            new ShadowSpeechDecorator(),
            new ShadowNarrationDecorator(),
            new ShadowLanguageComposer(),
        );
    }

    public function testStorytellerAddsNarrativeInstructions(): void
    {
        $preferences = ShadowIdentityPreferences::default()->withPersona(ShadowVoicePersona::Storyteller);

        $lines = $this->enricher->enrich(['You are Shadow.'], $preferences);

        self::assertTrue(
            (bool) array_filter(
                $lines,
                static fn (string $line): bool => str_contains($line, 'story'),
            ),
        );
    }

    public function testPersonaSuggestionForHistoryContent(): void
    {
        $engine = new ShadowPersonaSuggestionEngine();
        $suggestion = $engine->suggest('history');

        self::assertNotNull($suggestion);
        self::assertSame('storyteller', $suggestion['persona']);
    }

    public function testDocumentaryNarrationStyleHint(): void
    {
        $preferences = ShadowIdentityPreferences::default();
        $updated = new ShadowIdentityPreferences(
            $preferences->persona(),
            $preferences->personaTraits(),
            $preferences->voiceProfile(),
            $preferences->conversationStyle(),
            $preferences->teachingStyle(),
            ShadowNarrationStyle::Documentary,
            $preferences->languageProfile(),
            $preferences->answerStyle(),
            $preferences->challengeProfile(),
            $preferences->memoryPolicy(),
            $preferences->interruptionPolicy(),
            $preferences->thinkingStyle(),
            $preferences->humorLevel(),
            $preferences->curiosity(),
            $preferences->examplesLevel(),
            $preferences->storiesLevel(),
            $preferences->debateLevel(),
        );

        $lines = $this->enricher->enrich(['Base'], $updated);

        self::assertTrue(
            (bool) array_filter(
                $lines,
                static fn (string $line): bool => str_contains($line, 'documentary'),
            ),
        );
    }
}
