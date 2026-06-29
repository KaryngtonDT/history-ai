<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\ConversationId;
use App\Domain\Chat\Exception\InvalidConversationIdException;
use PHPUnit\Framework\TestCase;

final class ConversationIdTest extends TestCase
{
    public function testAcceptsValidUuid(): void
    {
        $id = new ConversationId('550e8400-e29b-41d4-a716-446655440000');

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $id->value);
    }

    public function testRejectsInvalidUuid(): void
    {
        $this->expectException(InvalidConversationIdException::class);

        new ConversationId('not-a-uuid');
    }

    public function testGenerateCreatesValidUuid(): void
    {
        $id = ConversationId::generate();

        self::assertTrue(ConversationId::isValid($id->value));
    }

    public function testEqualsComparesValue(): void
    {
        $left = new ConversationId('550e8400-e29b-41d4-a716-446655440000');
        $right = new ConversationId('550e8400-e29b-41d4-a716-446655440000');
        $other = ConversationId::generate();

        self::assertTrue($left->equals($right));
        self::assertFalse($left->equals($other));
    }
}
