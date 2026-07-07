<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shadow;

use App\Application\Shadow\Handlers\GetShadowContextHandler;
use App\Application\Shadow\Queries\GetShadowContextQuery;
use App\Application\Shadow\ShadowContextFactory;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowConversationContextInterface;
use App\Domain\Shadow\ShadowSessionRepositoryInterface;
use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptId;
use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use App\Domain\Translation\TranslationRepositoryInterface;
use App\Domain\Video\VideoId;
use App\Application\Shadow\CurrentSegmentResolver;
use App\Application\Shadow\TimelineContextBuilder;
use PHPUnit\Framework\TestCase;

final class GetShadowContextHandlerTest extends TestCase
{
    public function testReturnsWatchContextResult(): void
    {
        $videoId = VideoId::generate();
        $transcript = Transcript::create(
            TranscriptId::generate(),
            TranscriptLanguage::English,
            new TranscriptSegmentCollection([
                TranscriptSegment::create(0, 0.0, 5.0, 'Hello world.'),
            ]),
        );

        $transcriptRepository = $this->createStub(TranscriptRepositoryInterface::class);
        $transcriptRepository
            ->method('findByVideoId')
            ->willReturn($transcript);

        $translationRepository = $this->createStub(TranslationRepositoryInterface::class);
        $translationRepository
            ->method('findByVideoIdAndLanguage')
            ->willReturn(null);

        $sessionRepository = $this->createStub(ShadowSessionRepositoryInterface::class);
        $sessionRepository
            ->method('findByVideoId')
            ->willReturn([]);

        $conversationContext = $this->createStub(ShadowConversationContextInterface::class);

        $factory = new ShadowContextFactory(
            $transcriptRepository,
            $translationRepository,
            new CurrentSegmentResolver(),
            new TimelineContextBuilder(new CurrentSegmentResolver()),
            $sessionRepository,
            $conversationContext,
        );

        $handler = new GetShadowContextHandler($factory);
        $result = $handler(new GetShadowContextQuery($videoId->value, 2.5, 'fr'));

        self::assertSame($videoId->value, $result->videoId);
        self::assertSame(2.5, $result->currentTimeSeconds);
    }

    public function testRejectsInvalidVideoId(): void
    {
        $transcriptRepository = $this->createStub(TranscriptRepositoryInterface::class);
        $translationRepository = $this->createStub(TranslationRepositoryInterface::class);
        $sessionRepository = $this->createStub(ShadowSessionRepositoryInterface::class);
        $conversationContext = $this->createStub(ShadowConversationContextInterface::class);

        $factory = new ShadowContextFactory(
            $transcriptRepository,
            $translationRepository,
            new CurrentSegmentResolver(),
            new TimelineContextBuilder(new CurrentSegmentResolver()),
            $sessionRepository,
            $conversationContext,
        );

        $handler = new GetShadowContextHandler($factory);

        $this->expectException(InvalidShadowSessionException::class);
        $handler(new GetShadowContextQuery('not-a-uuid', 1.0, 'fr'));
    }
}
