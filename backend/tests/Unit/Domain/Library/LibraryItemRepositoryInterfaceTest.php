<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Library;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Content\ContentId;
use App\Domain\Library\LibraryItem;
use App\Domain\Library\LibraryItemId;
use App\Domain\Library\LibraryItemRepositoryInterface;
use App\Domain\Library\LibraryItemTitle;
use App\Domain\Library\LibraryItemType;
use PHPUnit\Framework\TestCase;

final class LibraryItemRepositoryInterfaceTest extends TestCase
{
    public function testRepositoryInterfaceDefinesExpectedMethods(): void
    {
        $repository = $this->createMock(LibraryItemRepositoryInterface::class);
        $item = LibraryItem::create(
            LibraryItemId::generate(),
            ContentId::generate(),
            ArtifactId::generate(),
            LibraryItemType::Flashcards,
            new LibraryItemTitle('Flashcards: Roman Empire'),
        );

        $repository
            ->expects(self::once())
            ->method('save')
            ->with($item);

        $repository
            ->expects(self::once())
            ->method('findById')
            ->with($item->id())
            ->willReturn($item);

        $repository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn([$item]);

        $repository->save($item);
        self::assertSame($item, $repository->findById($item->id()));
        self::assertSame([$item], $repository->findAll());
    }
}
