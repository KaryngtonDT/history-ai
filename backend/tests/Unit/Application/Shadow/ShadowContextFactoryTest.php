<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shadow;

use App\Application\Shadow\CurrentSegmentResolver;
use App\Application\Shadow\ShadowContextFactory;
use App\Application\Shadow\TimelineContextBuilder;
use App\Domain\Shadow\ShadowConversationContextInterface;
use App\Domain\Shadow\ShadowSessionRepositoryInterface;
use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptId;
use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use App\Domain\Translation\Translation;
use App\Domain\Translation\TranslationId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationRepositoryInterface;
use App\Domain\Translation\TranslationSegment;
use App\Domain\Translation\TranslationSegmentCollection;
use App\Domain\Video\VideoId;
use PHPUnit\Framework\TestCase;

final class ShadowContextFactoryTest extends TestCase
{
    private VideoId $videoId;

    protected function setUp(): void
    {
        $this->videoId = VideoId::generate();
    }

    public function testBuildsContextWithTranslation(): void
    {
        $factory = $this->createFactory(
            transcript: $this->sampleTranscript(),
            translation: $this->sampleTranslation(),
        );

        $context = $factory->create($this->videoId->value, 7.5, 'fr');

        self::assertSame($this->videoId->value, $context->videoId);
        self::assertNotNull($context->currentTranscriptSegment);
        self::assertSame(1, $context->currentTranscriptSegment->index);
        self::assertNotNull($context->currentTranslationSegment);
        self::assertSame('Deuxieme segment.', $context->currentTranslationSegment->translatedText);
        self::assertSame('Hello world. Second segment.', $context->nearbyTranscriptContext);
    }

    public function testFallsBackToTranscriptOnlyWhenTranslationMissing(): void
    {
        $factory = $this->createFactory(
            transcript: $this->sampleTranscript(),
            translation: null,
        );

        $context = $factory->create($this->videoId->value, 7.5, 'fr');

        self::assertNotNull($context->currentTranscriptSegment);
        self::assertNull($context->currentTranslationSegment);
        self::assertSame('', $context->nearbyTranslationContext);
    }

    public function testRejectsNegativeTime(): void
    {
        $factory = $this->createFactory(
            transcript: $this->sampleTranscript(),
            translation: null,
        );

        $this->expectException(\App\Domain\Shadow\Exception\InvalidShadowSessionException::class);
        $factory->create($this->videoId->value, -1.0, 'fr');
    }

    public function testRejectsMissingTranscript(): void
    {
        $factory = $this->createFactory(
            transcript: null,
            translation: null,
        );

        $this->expectException(\App\Domain\Shadow\Exception\InvalidShadowSessionException::class);
        $factory->create($this->videoId->value, 1.0, 'fr');
    }

    private function createFactory(?Transcript $transcript, ?Translation $translation): ShadowContextFactory
    {
        $transcriptRepository = $this->createMock(TranscriptRepositoryInterface::class);
        $transcriptRepository
            ->method('findByVideoId')
            ->willReturn($transcript);

        $translationRepository = $this->createMock(TranslationRepositoryInterface::class);
        $translationRepository
            ->method('findByVideoIdAndLanguage')
            ->willReturn($translation);

        $sessionRepository = $this->createMock(ShadowSessionRepositoryInterface::class);
        $sessionRepository
            ->method('findByVideoId')
            ->willReturn([]);

        $conversationContext = $this->createMock(ShadowConversationContextInterface::class);
        $conversationContext
            ->method('loadRecentMessages')
            ->willReturn([]);

        return new ShadowContextFactory(
            $transcriptRepository,
            $translationRepository,
            new CurrentSegmentResolver(),
            new TimelineContextBuilder(new CurrentSegmentResolver()),
            $sessionRepository,
            $conversationContext,
        );
    }

    private function sampleTranscript(): Transcript
    {
        return Transcript::create(
            TranscriptId::generate(),
            TranscriptLanguage::English,
            new TranscriptSegmentCollection([
                TranscriptSegment::create(0, 0.0, 5.0, 'Hello world.'),
                TranscriptSegment::create(1, 5.0, 10.0, 'Second segment.'),
            ]),
        );
    }

    private function sampleTranslation(): Translation
    {
        return Translation::create(
            TranslationId::generate(),
            TranslationLanguage::English,
            TranslationLanguage::French,
            TranslationProvider::Mock,
            new TranslationSegmentCollection([
                TranslationSegment::create(0, 'Hello world.', 'Bonjour le monde.'),
                TranslationSegment::create(1, 'Second segment.', 'Deuxieme segment.'),
            ]),
        );
    }
}
