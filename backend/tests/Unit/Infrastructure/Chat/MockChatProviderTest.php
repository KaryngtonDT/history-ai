<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Chat;

use App\Domain\Chat\ChatPrompt;
use App\Domain\Chat\ChatProviderInterface;
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
        $prompt = new ChatPrompt('Answer the question using only the document excerpts below.');
        $sources = ChatSourceCollection::empty();

        $answer = $provider->answer($prompt, $sources);

        self::assertSame(MockChatProvider::MOCK_ANSWER, $answer->answer());
        self::assertTrue($answer->sources()->isEmpty());
    }

    public function testReturnsProvidedSources(): void
    {
        $provider = new MockChatProvider();
        $sources = ChatSourceCollection::empty();

        $answer = $provider->answer(
            new ChatPrompt('Prompt with context'),
            $sources,
        );

        self::assertSame($sources, $answer->sources());
    }
}
