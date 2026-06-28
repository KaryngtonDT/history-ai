<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\ChatAnswer;
use App\Domain\Chat\ChatStream;
use App\Domain\Chat\ChatStreamEvent;
use App\Domain\Chat\ChatStreamEventCollection;
use App\Domain\Chat\ChatToken;
use App\Domain\Chat\Exception\InvalidChatQuestionException;
use PHPUnit\Framework\TestCase;

final class ChatStreamTest extends TestCase
{
    public function testExposesEvents(): void
    {
        $events = new ChatStreamEventCollection([
            new ChatStreamEvent(0, new ChatToken('Hello')),
        ]);
        $stream = new ChatStream($events);

        self::assertSame($events, $stream->events());
    }

    public function testToAnswerConcatenatesTokensInOrder(): void
    {
        $stream = new ChatStream(new ChatStreamEventCollection([
            new ChatStreamEvent(0, new ChatToken('Several ')),
            new ChatStreamEvent(1, new ChatToken('factors contributed.')),
        ]));

        $answer = $stream->toAnswer();

        self::assertInstanceOf(ChatAnswer::class, $answer);
        self::assertSame('Several factors contributed.', $answer->answer());
        self::assertTrue($answer->sources()->isEmpty());
    }

    public function testToAnswerTrimsFinalAnswer(): void
    {
        $stream = new ChatStream(new ChatStreamEventCollection([
            new ChatStreamEvent(0, new ChatToken('  Plain text answer  ')),
        ]));

        self::assertSame('Plain text answer', $stream->toAnswer()->answer());
    }

    public function testToAnswerRejectsEmptyStream(): void
    {
        $stream = new ChatStream(ChatStreamEventCollection::empty());

        $this->expectException(InvalidChatQuestionException::class);

        $stream->toAnswer();
    }

    public function testIsImmutable(): void
    {
        $events = ChatStreamEventCollection::empty();
        $stream = new ChatStream($events);

        self::assertSame($events, $stream->events());
        self::assertSame($events, $stream->events());
    }
}
