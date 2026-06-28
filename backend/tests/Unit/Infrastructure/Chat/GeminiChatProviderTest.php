<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Chat;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Chat\ChatModel;
use App\Domain\Chat\ChatPrompt;
use App\Domain\Chat\ChatProviderInterface;
use App\Domain\Chat\ChatProviderOptions;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\ChatResponse;
use App\Domain\Chat\ChatSource;
use App\Domain\Chat\ChatSourceCollection;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\RetrievedChunk;
use App\Domain\Semantic\SimilarityScore;
use App\Infrastructure\Chat\Exception\GeminiChatProviderException;
use App\Infrastructure\Chat\GeminiChatProvider;
use App\Infrastructure\Chat\GeminiChatTransportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class GeminiChatProviderTest extends TestCase
{
    private GeminiChatTransportInterface&MockObject $transport;

    protected function setUp(): void
    {
        $this->transport = $this->createMock(GeminiChatTransportInterface::class);
    }

    public function testImplementsChatProviderInterface(): void
    {
        $this->transport
            ->expects(self::never())
            ->method('generateContent');

        self::assertInstanceOf(
            ChatProviderInterface::class,
            new GeminiChatProvider($this->transport, 'test-api-key'),
        );
    }

    public function testSendsExpectedRequestPayloadWithPromptAndContext(): void
    {
        $request = $this->createRequest(
            "Answer the question using only the document excerpts below.\n\nQuestion:\nWhy did Rome fall?",
            [
                $this->createSource('## Ancient Rome', 0.87),
            ],
        );

        $this->transport
            ->expects(self::once())
            ->method('generateContent')
            ->with(self::callback(static function (array $payload) use ($request): bool {
                self::assertSame('gemini-2.5-flash', $payload['model'] ?? null);
                self::assertSame(0.2, $payload['generationConfig']['temperature'] ?? null);
                self::assertSame(1024, $payload['generationConfig']['maxOutputTokens'] ?? null);

                $text = $payload['contents'][0]['parts'][0]['text'] ?? null;
                self::assertIsString($text);
                self::assertStringContainsString($request->prompt()->value(), $text);
                self::assertStringContainsString('Retrieved sources:', $text);
                self::assertStringContainsString('## Ancient Rome', $text);
                self::assertStringContainsString('score=0.8700', $text);

                return true;
            }))
            ->willReturn($this->validResponse('Rome fell for several interconnected reasons.'));

        $response = $this->createProvider()->answer($request);

        self::assertSame('Rome fell for several interconnected reasons.', $response->answer());
        self::assertSame($request->sources(), $response->sources());
    }

    public function testMapsValidResponseToChatResponse(): void
    {
        $request = $this->createRequest('Prompt text');

        $this->transport
            ->expects(self::once())
            ->method('generateContent')
            ->willReturn($this->validResponse('  Grounded answer from Gemini.  '));

        $response = $this->createProvider()->answer($request);

        self::assertInstanceOf(ChatResponse::class, $response);
        self::assertSame('Grounded answer from Gemini.', $response->answer());
    }

    public function testUsesConfiguredDefaultModel(): void
    {
        $this->transport
            ->expects(self::once())
            ->method('generateContent')
            ->with(self::callback(static fn (array $payload): bool => 'gemini-2.5-flash' === ($payload['model'] ?? null)))
            ->willReturn($this->validResponse('Answer'));

        $this->createProvider()->answer($this->createRequest('Prompt text'));
    }

    public function testUsesRequestModelOverrideWhenProvided(): void
    {
        $options = new ChatProviderOptions(model: new ChatModel('gemini-2.0-pro'));
        $request = ChatRequest::create(
            new ChatPrompt('Prompt text'),
            ChatSourceCollection::empty(),
            $options,
        );

        $this->transport
            ->expects(self::once())
            ->method('generateContent')
            ->with(self::callback(static fn (array $payload): bool => 'gemini-2.0-pro' === ($payload['model'] ?? null)))
            ->willReturn($this->validResponse('Answer'));

        $this->createProvider()->answer($request);
    }

    public function testRejectsEmptyAnswerResponse(): void
    {
        $this->transport
            ->expects(self::once())
            ->method('generateContent')
            ->willReturn([
                'candidates' => [
                    ['content' => ['parts' => [['text' => '   ']]]],
                ],
            ]);

        $this->expectException(GeminiChatProviderException::class);
        $this->expectExceptionMessage('empty answer');

        $this->createProvider()->answer($this->createRequest('Prompt text'));
    }

    public function testRejectsMissingCandidatesResponse(): void
    {
        $this->transport
            ->expects(self::once())
            ->method('generateContent')
            ->willReturn([]);

        $this->expectException(GeminiChatProviderException::class);
        $this->expectExceptionMessage('no candidates');

        $this->createProvider()->answer($this->createRequest('Prompt text'));
    }

    public function testWrapsTransportErrorsInGeminiChatProviderException(): void
    {
        $this->transport
            ->expects(self::once())
            ->method('generateContent')
            ->willThrowException(new RuntimeException('Connection refused'));

        $this->expectException(GeminiChatProviderException::class);
        $this->expectExceptionMessage('Gemini chat request failed.');

        $this->createProvider()->answer($this->createRequest('Prompt text'));
    }

    public function testRequiresConfiguredApiKey(): void
    {
        $this->transport
            ->expects(self::never())
            ->method('generateContent');

        $provider = new GeminiChatProvider(
            $this->transport,
            '',
            GeminiChatProvider::DEFAULT_MODEL,
        );

        $this->expectException(GeminiChatProviderException::class);
        $this->expectExceptionMessage('GEMINI_API_KEY is not configured.');

        $provider->answer($this->createRequest('Prompt text'));
    }

    public function testDoesNotCallTransportWhenApiKeyMissing(): void
    {
        $this->transport
            ->expects(self::never())
            ->method('generateContent');

        $provider = new GeminiChatProvider(
            $this->transport,
            '   ',
            GeminiChatProvider::DEFAULT_MODEL,
        );

        $this->expectException(GeminiChatProviderException::class);

        $provider->answer($this->createRequest('Prompt text'));
    }

    /**
     * @param list<ChatSource> $sources
     */
    private function createRequest(string $prompt, array $sources = []): ChatRequest
    {
        return ChatRequest::create(
            new ChatPrompt($prompt),
            new ChatSourceCollection($sources),
        );
    }

    private function createSource(string $text, float $score): ChatSource
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440003');
        $position = new ChunkPosition(0);

        return ChatSource::fromRetrievedChunk(
            new RetrievedChunk(
                new Chunk(
                    ChunkId::derive($artifactId, $position),
                    $artifactId,
                    ChunkText::fromString($text),
                    $position,
                ),
                new SimilarityScore($score),
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validResponse(string $text): array
    {
        return [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => $text],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function createProvider(): GeminiChatProvider
    {
        return new GeminiChatProvider(
            $this->transport,
            'test-api-key',
            GeminiChatProvider::DEFAULT_MODEL,
        );
    }
}
