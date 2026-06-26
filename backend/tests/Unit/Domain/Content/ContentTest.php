<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Content;

use App\Domain\Content\Content;
use App\Domain\Content\ContentId;
use App\Domain\Content\ContentSourceType;
use App\Domain\Content\ContentStatus;
use App\Domain\Content\ContentTitle;
use App\Domain\Content\Exception\InvalidContentTitleException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContentTest extends TestCase
{
    public function testCreateContentWithDefaultDraftStatus(): void
    {
        $content = Content::create(
            ContentId::generate(),
            new ContentTitle('Introduction to Roman History'),
            ContentSourceType::UploadPdf,
        );

        self::assertSame(ContentStatus::Draft, $content->status());
        self::assertSame(ContentSourceType::UploadPdf, $content->sourceType());
        self::assertSame('Introduction to Roman History', $content->title()->value);
        self::assertNotEmpty($content->id()->value);
        self::assertLessThanOrEqual($content->createdAt(), $content->updatedAt());
    }

    #[DataProvider('invalidTitleProvider')]
    public function testEmptyTitleIsRejected(string $title): void
    {
        $this->expectException(InvalidContentTitleException::class);

        new ContentTitle($title);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidTitleProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'whitespace only' => ['   '];
    }
}
