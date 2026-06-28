<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\ChatQuestion;
use App\Domain\Chat\Exception\InvalidChatQuestionException;
use PHPUnit\Framework\TestCase;

final class ChatQuestionTest extends TestCase
{
    public function testTrimsWhitespace(): void
    {
        $question = new ChatQuestion('  Why did Rome fall?  ');

        self::assertSame('Why did Rome fall?', $question->value());
    }

    public function testRejectsEmptyQuestion(): void
    {
        $this->expectException(InvalidChatQuestionException::class);

        new ChatQuestion('');
    }

    public function testRejectsWhitespaceOnlyQuestion(): void
    {
        $this->expectException(InvalidChatQuestionException::class);

        new ChatQuestion('   ');
    }

    public function testRejectsQuestionAboveMaxLength(): void
    {
        $this->expectException(InvalidChatQuestionException::class);

        new ChatQuestion(str_repeat('a', 2001));
    }

    public function testAcceptsQuestionAtMaxLength(): void
    {
        $value = str_repeat('a', 2000);

        $question = new ChatQuestion($value);

        self::assertSame($value, $question->value());
    }

    public function testEqualsComparesValue(): void
    {
        self::assertTrue((new ChatQuestion('Roman Empire'))->equals(new ChatQuestion('Roman Empire')));
        self::assertFalse((new ChatQuestion('Roman Empire'))->equals(new ChatQuestion('Greek Empire')));
    }
}
