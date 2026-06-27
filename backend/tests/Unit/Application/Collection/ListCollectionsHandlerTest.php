<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Collection;

use App\Application\Collection\DTO\CollectionListItem;
use App\Application\Collection\Handlers\ListCollectionsHandler;
use App\Application\Collection\Queries\ListCollectionsQuery;
use App\Domain\Collection\Collection;
use App\Domain\Collection\CollectionDescription;
use App\Domain\Collection\CollectionId;
use App\Domain\Collection\CollectionName;
use App\Domain\Collection\CollectionRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class ListCollectionsHandlerTest extends TestCase
{
    public function testReturnsEmptyListWhenNoCollectionsExist(): void
    {
        $repository = $this->createStub(CollectionRepositoryInterface::class);
        $repository->method('findAll')->willReturn([]);

        $handler = new ListCollectionsHandler($repository);
        $result = $handler(new ListCollectionsQuery());

        self::assertSame([], $result->collections);
    }

    public function testReturnsCollectionsFromRepository(): void
    {
        $collection = Collection::create(
            CollectionId::generate(),
            new CollectionName('Ancient Rome'),
            new CollectionDescription('Resources about Roman history'),
        );

        $repository = $this->createStub(CollectionRepositoryInterface::class);
        $repository->method('findAll')->willReturn([$collection]);

        $handler = new ListCollectionsHandler($repository);
        $result = $handler(new ListCollectionsQuery());

        self::assertCount(1, $result->collections);
        self::assertInstanceOf(CollectionListItem::class, $result->collections[0]);
        self::assertSame($collection->id()->value, $result->collections[0]->id);
        self::assertSame('Ancient Rome', $result->collections[0]->name);
        self::assertSame('Resources about Roman history', $result->collections[0]->description);
    }
}
