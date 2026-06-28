<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\ChatPrompt;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\ChatSourceCollection;
use App\Domain\Chat\ChatStream;
use App\Domain\Chat\ChatStreamEvent;
use App\Domain\Chat\ChatStreamEventCollection;
use App\Domain\Chat\ChatToken;
use App\Domain\Chat\StreamingChatProviderInterface;
use PHPUnit\Framework\TestCase;

final class StreamingChatProviderInterfaceTest extends TestCase
{
    public function testProviderInterfaceDefinesStreamMethod(): void
    {
        $request = ChatRequest::create(
            new ChatPrompt('Answer the question using only the document excerpts below.'),
            ChatSourceCollection::empty(),
        );
        $expected = new ChatStream(new ChatStreamEventCollection([
            new ChatStreamEvent(0, new ChatToken('Mock ')),
            new ChatStreamEvent(1, new ChatToken('answer ')),
        ]));

        $provider = $this->createMock(StreamingChatProviderInterface::class);
        $provider
            ->expects(self::once())
            ->method('stream')
            ->with($request)
            ->willReturn($expected);

        self::assertSame($expected, $provider->stream($request));
    }
}
