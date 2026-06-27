<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Library;

use App\Application\Library\Commands\AddLibraryItemCommand;
use App\Application\Library\Handlers\AddLibraryItemHandler;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\Exception\InvalidArtifactException;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Domain\Library\Exception\InvalidLibraryItemTitleException;
use App\Domain\Library\LibraryItem;
use App\Domain\Library\LibraryItemRepositoryInterface;
use App\Domain\Library\LibraryItemType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AddLibraryItemHandlerTest extends TestCase
{
    public function testValidCommandCreatesLibraryItem(): void
    {
        $contentId = ContentId::generate();
        $artifactId = ArtifactId::generate();
        $repository = $this->createMock(LibraryItemRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (LibraryItem $item) use ($contentId, $artifactId): bool {
                return $contentId->equals($item->contentId())
                    && $artifactId->equals($item->artifactId())
                    && LibraryItemType::Summary === $item->type()
                    && 'Roman Empire Summary' === $item->title()->value;
            }));

        $handler = new AddLibraryItemHandler($repository);

        $result = $handler(new AddLibraryItemCommand(
            contentId: $contentId->value,
            artifactId: $artifactId->value,
            type: LibraryItemType::Summary,
            title: 'Roman Empire Summary',
        ));

        self::assertNotEmpty($result->libraryItemId->value);
        self::assertSame(LibraryItemType::Summary, $result->type);
        self::assertSame('Roman Empire Summary', $result->title->value);
        self::assertInstanceOf(\DateTimeImmutable::class, $result->createdAt);
    }

    public function testRepositorySaveIsCalledExactlyOnce(): void
    {
        $repository = $this->createMock(LibraryItemRepositoryInterface::class);
        $repository->expects(self::once())->method('save');

        $handler = new AddLibraryItemHandler($repository);

        $handler(new AddLibraryItemCommand(
            contentId: ContentId::generate()->value,
            artifactId: ArtifactId::generate()->value,
            type: LibraryItemType::Quiz,
            title: 'Quiz: Roman Empire',
        ));
    }

    #[DataProvider('invalidTitleProvider')]
    public function testInvalidTitleIsRejectedByDomain(string $title): void
    {
        $repository = $this->createMock(LibraryItemRepositoryInterface::class);
        $repository->expects(self::never())->method('save');

        $handler = new AddLibraryItemHandler($repository);

        $this->expectException(InvalidLibraryItemTitleException::class);

        $handler(new AddLibraryItemCommand(
            contentId: ContentId::generate()->value,
            artifactId: ArtifactId::generate()->value,
            type: LibraryItemType::Flashcards,
            title: $title,
        ));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidTitleProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'whitespace only' => ['   '];
        yield 'tab and newline' => ["\t\n"];
    }

    public function testInvalidContentIdIsRejected(): void
    {
        $repository = $this->createMock(LibraryItemRepositoryInterface::class);
        $repository->expects(self::never())->method('save');

        $handler = new AddLibraryItemHandler($repository);

        $this->expectException(InvalidContentIdException::class);

        $handler(new AddLibraryItemCommand(
            contentId: 'not-a-valid-uuid',
            artifactId: ArtifactId::generate()->value,
            type: LibraryItemType::Summary,
            title: 'Valid title',
        ));
    }

    public function testInvalidArtifactIdIsRejected(): void
    {
        $repository = $this->createMock(LibraryItemRepositoryInterface::class);
        $repository->expects(self::never())->method('save');

        $handler = new AddLibraryItemHandler($repository);

        $this->expectException(InvalidArtifactException::class);

        $handler(new AddLibraryItemCommand(
            contentId: ContentId::generate()->value,
            artifactId: 'not-a-valid-uuid',
            type: LibraryItemType::Summary,
            title: 'Valid title',
        ));
    }
}
