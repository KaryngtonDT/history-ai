<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Shadow;

use App\Application\Shadow\Commands\AskShadowQuestionCommand;
use App\Application\Shadow\Commands\PauseShadowSessionCommand;
use App\Application\Shadow\Commands\ResumeShadowSessionCommand;
use App\Application\Shadow\Commands\StartShadowSessionCommand;
use App\Application\Shadow\CurrentSegmentResolver;
use App\Application\Shadow\Handlers\AskShadowQuestionHandler;
use App\Application\Shadow\Handlers\PauseShadowSessionHandler;
use App\Application\Shadow\Handlers\ResumeShadowSessionHandler;
use App\Application\Shadow\Handlers\StartShadowSessionHandler;
use App\Application\Shadow\ShadowContextFactory;
use App\Application\Shadow\ShadowSessionResolver;
use App\Application\Shadow\ShadowWatchAnswerer;
use App\Application\Shadow\ShadowWatchPromptBuilder;
use App\Application\Shadow\TimelineContextBuilder;
use App\Domain\Chat\ChatProviderInterface;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\ChatResponse;
use App\Domain\Chat\ChatSourceCollection;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowConversationContextInterface;
use App\Domain\Shadow\ShadowSession;
use App\Domain\Shadow\ShadowSessionId;
use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptId;
use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use App\Domain\Translation\TranslationRepositoryInterface;
use App\Domain\Video\VideoId;
use App\Infrastructure\Chat\MockChatProvider;
use App\Infrastructure\Shadow\InMemoryShadowSessionRepository;
use PHPUnit\Framework\TestCase;

final class ShadowHandlersTest extends TestCase
{
    private InMemoryShadowSessionRepository $sessionRepository;

    private VideoId $videoId;

    protected function setUp(): void
    {
        $this->sessionRepository = new InMemoryShadowSessionRepository();
        $this->videoId = VideoId::generate();
    }

    public function testStartSessionPersistsWatchSession(): void
    {
        $handler = new StartShadowSessionHandler(
            $this->sessionRepository,
            $this->transcriptRepository(),
            new ShadowSessionResolver($this->sessionRepository),
        );

        $result = $handler(new StartShadowSessionCommand(
            videoId: $this->videoId->value,
            targetLanguage: 'fr',
        ));

        self::assertSame('playing', $result->playbackState);
        self::assertSame('fr', $result->targetLanguage);
        self::assertNotNull($this->sessionRepository->findById(new ShadowSessionId($result->sessionId)));
    }

    public function testAskQuestionStoresInteractionHistory(): void
    {
        $session = $this->startSampleSession();
        $handler = $this->askHandler(new MockChatProvider());

        $result = $handler(new AskShadowQuestionCommand(
            videoId: $this->videoId->value,
            sessionId: $session->id()->value,
            question: 'Explain this sentence.',
            currentTimeSeconds: 2.5,
        ));

        self::assertStringContainsString('Mock answer', $result->answer);
        self::assertCount(2, $result->session->interactions);

        $stored = $this->sessionRepository->findById($session->id());
        self::assertNotNull($stored);
        self::assertCount(2, $stored->interactions()->all());
    }

    public function testPauseAndResumeUpdatePlaybackState(): void
    {
        $session = $this->startSampleSession();
        $resolver = new ShadowSessionResolver($this->sessionRepository);
        $pauseHandler = new PauseShadowSessionHandler($this->sessionRepository, $resolver);
        $resumeHandler = new ResumeShadowSessionHandler($this->sessionRepository, $resolver);

        $paused = $pauseHandler(new PauseShadowSessionCommand(
            videoId: $this->videoId->value,
            sessionId: $session->id()->value,
            currentTimeSeconds: 4.0,
        ));
        self::assertSame('paused', $paused->playbackState);

        $resumed = $resumeHandler(new ResumeShadowSessionCommand(
            videoId: $this->videoId->value,
            sessionId: $session->id()->value,
        ));
        self::assertSame('playing', $resumed->playbackState);
    }

    public function testAskReturnsFallbackWhenProviderFails(): void
    {
        $session = $this->startSampleSession();
        $failingProvider = new class implements ChatProviderInterface {
            public function answer(ChatRequest $request): ChatResponse
            {
                throw new \RuntimeException('provider down');
            }
        };

        $handler = $this->askHandler($failingProvider);
        $result = $handler(new AskShadowQuestionCommand(
            videoId: $this->videoId->value,
            sessionId: $session->id()->value,
            question: 'What does this word mean?',
            currentTimeSeconds: 2.5,
        ));

        self::assertSame(ShadowWatchAnswerer::FALLBACK_ANSWER, $result->answer);
    }

    public function testInvalidSessionIsRejected(): void
    {
        $handler = $this->askHandler(new MockChatProvider());

        $this->expectException(InvalidShadowSessionException::class);
        $handler(new AskShadowQuestionCommand(
            videoId: $this->videoId->value,
            sessionId: ShadowSessionId::generate()->value,
            question: 'Explain this.',
            currentTimeSeconds: 1.0,
        ));
    }

    private function startSampleSession(): ShadowSession
    {
        $handler = new StartShadowSessionHandler(
            $this->sessionRepository,
            $this->transcriptRepository(),
            new ShadowSessionResolver($this->sessionRepository),
        );

        $result = $handler(new StartShadowSessionCommand(
            videoId: $this->videoId->value,
            targetLanguage: 'fr',
        ));

        $session = $this->sessionRepository->findById(new ShadowSessionId($result->sessionId));
        self::assertInstanceOf(\App\Domain\Shadow\ShadowSession::class, $session);

        return $session;
    }

    private function askHandler(ChatProviderInterface $chatProvider): AskShadowQuestionHandler
    {
        return new AskShadowQuestionHandler(
            $this->sessionRepository,
            new ShadowSessionResolver($this->sessionRepository),
            $this->shadowContextFactory(),
            new ShadowWatchAnswerer($chatProvider, new ShadowWatchPromptBuilder()),
        );
    }

    private function shadowContextFactory(): ShadowContextFactory
    {
        $conversationContext = $this->createMock(ShadowConversationContextInterface::class);
        $conversationContext->method('loadRecentMessages')->willReturn([]);

        return new ShadowContextFactory(
            $this->transcriptRepository(),
            $this->translationRepository(),
            new CurrentSegmentResolver(),
            new TimelineContextBuilder(new CurrentSegmentResolver()),
            $this->sessionRepository,
            $conversationContext,
        );
    }

    private function transcriptRepository(): TranscriptRepositoryInterface
    {
        $repository = $this->createMock(TranscriptRepositoryInterface::class);
        $repository
            ->method('findByVideoId')
            ->willReturn(Transcript::create(
                TranscriptId::generate(),
                TranscriptLanguage::English,
                new TranscriptSegmentCollection([
                    TranscriptSegment::create(0, 0.0, 5.0, 'Hello world.'),
                    TranscriptSegment::create(1, 5.0, 10.0, 'Second segment.'),
                ]),
            ));

        return $repository;
    }

    private function translationRepository(): TranslationRepositoryInterface
    {
        $repository = $this->createMock(TranslationRepositoryInterface::class);
        $repository->method('findByVideoIdAndLanguage')->willReturn(null);

        return $repository;
    }
}
