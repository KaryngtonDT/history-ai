<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Chat;

use App\Domain\Chat\ChatPrompt;
use App\Domain\Chat\ChatProviderInterface;
use App\Domain\Chat\ChatProviderOptions;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\ChatSourceCollection;
use App\Infrastructure\Chat\MockChatProvider;
use PHPUnit\Framework\TestCase;

final class MockChatProviderTest extends TestCase
{
    public function testImplementsChatProviderInterface(): void
    {
        self::assertInstanceOf(ChatProviderInterface::class, new MockChatProvider());
    }

    public function testReturnsDeterministicMockAnswer(): void
    {
        $provider = new MockChatProvider();
        $request = ChatRequest::create(
            new ChatPrompt('Answer the question using only the document excerpts below.'),
            ChatSourceCollection::empty(),
        );

        $response = $provider->answer($request);

        self::assertSame(MockChatProvider::MOCK_ANSWER, $response->answer());
        self::assertTrue($response->sources()->isEmpty());
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
}
