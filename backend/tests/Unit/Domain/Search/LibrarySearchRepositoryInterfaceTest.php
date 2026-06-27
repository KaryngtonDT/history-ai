<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Search;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Content\ContentId;
use App\Domain\Library\LibraryItem;
use App\Domain\Library\LibraryItemId;
use App\Domain\Library\LibraryItemTitle;
use App\Domain\Library\LibraryItemType;
use App\Domain\Search\LibrarySearchRepositoryInterface;
use App\Domain\Search\SearchQuery;
use PHPUnit\Framework\TestCase;

final class LibrarySearchRepositoryInterfaceTest extends TestCase
{
    public function testRepositoryInterfaceDefinesSearchMethod(): void
    {
        $repository = $this->createMock(LibrarySearchRepositoryInterface::class);
        $query = new SearchQuery('Roman Empire');
        $item = LibraryItem::create(
            LibraryItemId::generate(),
            ContentId::generate(),
            ArtifactId::generate(),
            LibraryItemType::Summary,
            new LibraryItemTitle('Summary: Roman Empire'),
        );

        $repository
            ->expects(self::once())
            ->method('search')
            ->with($query)
            ->willReturn([$item]);

        self::assertSame([$item], $repository->search($query));
    }
}
