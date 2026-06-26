<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Content;

use App\Application\Content\DTO\ContentListItem;
use App\Application\Content\Handlers\ListContentsHandler;
use App\Application\Content\Queries\ListContentsQuery;
use App\Domain\Content\Content;
use App\Domain\Content\ContentId;
use App\Domain\Content\ContentRepositoryInterface;
use App\Domain\Content\ContentSourceType;
use App\Domain\Content\ContentTitle;
use PHPUnit\Framework\TestCase;

final class ListContentsHandlerTest extends TestCase
{
    public function testReturnsEmptyListWhenNoContentsExist(): void
    {
        $repository = $this->createStub(ContentRepositoryInterface::class);
        $repository->method('findAll')->willReturn([]);

        $handler = new ListContentsHandler($repository);
        $result = $handler(new ListContentsQuery());

        self::assertSame([], $result->items);
    }

    public function testMapsContentsToListItems(): void
    {
        $content = Content::create(
            ContentId::generate(),
            new ContentTitle('The Roman Empire'),
            ContentSourceType::YoutubeUrl,
        );

        $repository = $this->createStub(ContentRepositoryInterface::class);
        $repository->method('findAll')->willReturn([$content]);

        $handler = new ListContentsHandler($repository);
        $result = $handler(new ListContentsQuery());

        self::assertCount(1, $result->items);
        self::assertInstanceOf(ContentListItem::class, $result->items[0]);
        self::assertSame('The Roman Empire', $result->items[0]->title);
        self::assertSame('youtube_url', $result->items[0]->sourceType);
        self::assertSame('draft', $result->items[0]->status);
    }
}
