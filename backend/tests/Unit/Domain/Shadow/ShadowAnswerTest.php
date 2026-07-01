<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shadow;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowAnswer;
use PHPUnit\Framework\TestCase;

final class ShadowAnswerTest extends TestCase
{
    public function testTrimsAnswerText(): void
    {
        $answer = ShadowAnswer::fromString('  It means growth on prior interest.  ');
        self::assertSame('It means growth on prior interest.', $answer->text());
    }

    public function testRejectsEmptyAnswer(): void
    {
        $this->expectException(InvalidShadowSessionException::class);
        ShadowAnswer::fromString('');
    }
}
