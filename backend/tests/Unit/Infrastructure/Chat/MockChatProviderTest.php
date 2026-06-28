<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Chat;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Chat\ChatPrompt;
use App\Domain\Chat\ChatProviderInterface;
use App\Domain\Chat\ChatProviderOptions;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\ChatSource;
use App\Domain\Chat\ChatSourceCollection;
use App\Domain\Chat\ChatStreamEvent;
use App\Domain\Chat\StreamingChatProviderInterface;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\RetrievedChunk;
use App\Domain\Semantic\SimilarityScore;
use App\Infrastructure\Chat\MockChatProvider;
use PHPUnit\Framework\TestCase;

final class MockChatProviderTest extends TestCase
{
    public function testImplementsChatProviderInterface(): void
    {
        self::assertInstanceOf(ChatProviderInterface::class, new MockChatProvider());
    }

    public function testImplementsStreamingChatProviderInterface(): void
    {
        self::assertInstanceOf(StreamingChatProviderInterface::class, new MockChatProvider());
    }

    public function testReturnsDeterministicMockAnswerWithoutSources(): void
    {
        $provider = new MockChatProvider();
        $request = ChatRequest::create(
            new ChatPrompt('Answer the question using only the document excerpts below.'),
            ChatSourceCollection::empty(),
        );

        $response = $provider->answer($request);

        self::assertSame(MockChatProvider::MOCK_ANSWER, $response->answer());
        self::assertTrue($response->sources()->isEmpty());
        self::assertTrue($response->citations()->isEmpty());
    }

    public function testReturnsProvidedSources(): void
    {
        $provider = new MockChatProvider();
        $sources = ChatSourceCollection::empty();
        $request = ChatRequest::create(
            new ChatPrompt('Prompt with context'),
            $sources,
        );

        $response = $provider->answer($request);

        self::assertSame($sources, $response->sources());
    }

    public function testReceivesChatRequestWithOptions(): void
    {
        $provider = new MockChatProvider();
        $options = ChatProviderOptions::defaults();
        $request = ChatRequest::create(
            new ChatPrompt('Prompt with context'),
            ChatSourceCollection::empty(),
            $options,
        );

        $response = $provider->answer($request);

        self::assertSame(MockChatProvider::MOCK_ANSWER, $response->answer());
    }

    public function testStreamReturnsOrderedTokensWithoutSources(): void
    {
        $provider = new MockChatProvider();
        $request = ChatRequest::create(
            new ChatPrompt('Answer the question using only the document excerpts below.'),
            ChatSourceCollection::empty(),
        );

        $stream = $provider->stream($request);

        self::assertSame(
            [
                'Mock ',
                'answer ',
                'based ',
                'on ',
                'retrieved ',
                'context ',
                '.',
            ],
            array_map(
                static fn (ChatStreamEvent $event): string => $event->token()->text(),
                $stream->events()->events(),
            ),
        );
        self::assertSame(
            [0, 1, 2, 3, 4, 5, 6],
            array_map(
                static fn (ChatStreamEvent $event): int => $event->index(),
                $stream->events()->events(),
            ),
        );
    }

    public function testStreamReturnsOrderedTokensWithSources(): void
    {
        $provider = new MockChatProvider();
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $source = $this->createSource($artifactId, 0, 'Summary excerpt', 0.9);
        $request = ChatRequest::create(
            new ChatPrompt('Prompt with context'),
            new ChatSourceCollection([$source]),
        );

        $stream = $provider->stream($request);

        self::assertSame(
            [
                'Mock ',
                'answer ',
                'based ',
                'on ',
                'retrieved ',
                'context ',
                '[1].',
            ],
            array_map(
                static fn (ChatStreamEvent $event): string => $event->token()->text(),
                $stream->events()->events(),
            ),
        );
    }

    public function testStreamToAnswerMatchesNonStreamingAnswerText(): void
    {
        $provider = new MockChatProvider();
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $firstSource = $this->createSource($artifactId, 0, 'Summary excerpt', 0.9);
        $secondSource = $this->createSource($artifactId, 1, 'Timeline excerpt', 0.8);
        $sources = new ChatSourceCollection([$firstSource, $secondSource]);
        $request = ChatRequest::create(
            new ChatPrompt('Prompt with context'),
            $sources,
        );

        self::assertSame(
            $provider->answer($request)->answer(),
            $provider->stream($request)->toAnswer()->answer(),
        );
    }

    public function testGeneratesDeterministicCitationsFromSources(): void
    {
        $provider = new MockChatProvider();
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $firstSource = $this->createSource($artifactId, 0, 'Summary excerpt', 0.9);
        $secondSource = $this->createSource($artifactId, 1, 'Timeline excerpt', 0.8);
        $sources = new ChatSourceCollection([$firstSource, $secondSource]);
        $request = ChatRequest::create(
            new ChatPrompt('Prompt with context'),
            $sources,
        );

        $response = $provider->answer($request);

        self::assertSame(
            'Mock answer based on retrieved context [1][2].',
            $response->answer(),
        );
        self::assertSame($sources, $response->sources());
        self::assertSame(2, $response->citations()->count());
        self::assertSame(1, $response->citations()->citations()[0]->number());
        self::assertTrue($response->citations()->citations()[0]->source()->equals($firstSource));
        self::assertSame(2, $response->citations()->citations()[1]->number());
        self::assertTrue($response->citations()->citations()[1]->source()->equals($secondSource));
    }

    private function createSource(
        ArtifactId $artifactId,
        int $position,
        string $text,
        float $score,
    ): ChatSource {
        $chunkPosition = new ChunkPosition($position);

        return ChatSource::fromRetrievedChunk(
            new RetrievedChunk(
                new Chunk(
                    ChunkId::derive($artifactId, $chunkPosition),
                    $artifactId,
                    ChunkText::fromString($text),
                    $chunkPosition,
                ),
                new SimilarityScore($score),
            ),
        );
    }
}
