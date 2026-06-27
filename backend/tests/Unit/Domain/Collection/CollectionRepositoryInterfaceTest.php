<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Collection;

use App\Domain\Collection\Collection;
use App\Domain\Collection\CollectionDescription;
use App\Domain\Collection\CollectionId;
use App\Domain\Collection\CollectionName;
use App\Domain\Collection\CollectionRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class CollectionRepositoryInterfaceTest extends TestCase
{
    public function testRepositoryInterfaceDefinesExpectedMethods(): void
    {
        $repository = $this->createMock(CollectionRepositoryInterface::class);
        $collection = Collection::create(
            CollectionId::generate(),
            new CollectionName('Ancient Rome'),
            new CollectionDescription('Roman history materials.'),
        );

        $repository
            ->expects(self::once())
            ->method('save')
            ->with($collection);

        $repository
            ->expects(self::once())
            ->method('findById')
            ->with($collection->id())
            ->willReturn($collection);

        $repository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn([$collection]);

        $repository->save($collection);
        self::assertSame($collection, $repository->findById($collection->id()));
        self::assertSame([$collection], $repository->findAll());
    }
}
