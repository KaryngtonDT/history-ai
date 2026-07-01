<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shadow;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowQuestion;
use PHPUnit\Framework\TestCase;

final class ShadowQuestionTest extends TestCase
{
    public function testTrimsQuestionText(): void
    {
        $question = ShadowQuestion::fromString('  Explain this sentence.  ');
        self::assertSame('Explain this sentence.', $question->text());
    }

    public function testRejectsEmptyQuestion(): void
    {
        $this->expectException(InvalidShadowSessionException::class);
        ShadowQuestion::fromString('   ');
    }
}
