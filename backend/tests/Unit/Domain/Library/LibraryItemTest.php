<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Library;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Content\ContentId;
use App\Domain\Library\Exception\InvalidLibraryItemException;
use App\Domain\Library\Exception\InvalidLibraryItemTitleException;
use App\Domain\Library\LibraryItem;
use App\Domain\Library\LibraryItemId;
use App\Domain\Library\LibraryItemTitle;
use App\Domain\Library\LibraryItemType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class LibraryItemTest extends TestCase
{
    public function testCreateValidLibraryItem(): void
    {
        $id = LibraryItemId::generate();
        $contentId = ContentId::generate();
        $artifactId = ArtifactId::generate();
        $title = new LibraryItemTitle('Roman Empire Summary');

        $item = LibraryItem::create(
            $id,
            $contentId,
            $artifactId,
            LibraryItemType::Summary,
            $title,
        );

        self::assertTrue($item->id()->equals($id));
        self::assertTrue($item->contentId()->equals($contentId));
        self::assertTrue($item->artifactId()->equals($artifactId));
        self::assertSame(LibraryItemType::Summary, $item->type());
        self::assertTrue($item->title()->equals($title));
        self::assertSame('Roman Empire Summary', $item->title()->value);
        self::assertLessThanOrEqual(new \DateTimeImmutable(), $item->createdAt());
    }

    #[DataProvider('libraryItemTypeProvider')]
    public function testSupportsAllLibraryItemTypes(LibraryItemType $type): void
    {
        $item = LibraryItem::create(
            LibraryItemId::generate(),
            ContentId::generate(),
            ArtifactId::generate(),
            $type,
            new LibraryItemTitle('Sample library item'),
        );

        self::assertSame($type, $item->type());
    }

    /**
     * @return iterable<string, array{LibraryItemType}>
     */
    public static function libraryItemTypeProvider(): iterable
    {
        yield 'summary' => [LibraryItemType::Summary];
        yield 'quiz' => [LibraryItemType::Quiz];
        yield 'flashcards' => [LibraryItemType::Flashcards];
        yield 'transcript' => [LibraryItemType::Transcript];
        yield 'translation' => [LibraryItemType::Translation];
        yield 'timeline' => [LibraryItemType::Timeline];
        yield 'podcast' => [LibraryItemType::Podcast];
    }

    public function testAggregateIsImmutable(): void
    {
        $reflection = new ReflectionClass(LibraryItem::class);

        self::assertTrue($reflection->isFinal());

        foreach ($reflection->getProperties() as $property) {
            self::assertTrue(
                $property->isReadOnly(),
                sprintf('Property %s must be readonly.', $property->getName()),
            );
            self::assertTrue(
                $property->isPrivate(),
                sprintf('Property %s must be private.', $property->getName()),
            );
        }
    }

    public function testAggregateHasNoMutators(): void
    {
        $reflection = new ReflectionClass(LibraryItem::class);
        $allowedMethods = [
            'create',
            'reconstitute',
            'id',
            'contentId',
            'artifactId',
            'type',
            'title',
            'createdAt',
        ];

        $mutators = array_filter(
            $reflection->getMethods(\ReflectionMethod::IS_PUBLIC),
            static fn (\ReflectionMethod $method): bool => !in_array($method->getName(), $allowedMethods, true)
                && !str_starts_with($method->getName(), '__'),
        );

        self::assertSame([], array_map(
            static fn (\ReflectionMethod $method): string => $method->getName(),
            $mutators,
        ));
    }

    #[DataProvider('invalidTitleProvider')]
    public function testInvalidTitleIsRejected(string $title): void
    {
        $this->expectException(InvalidLibraryItemTitleException::class);
        $this->expectExceptionMessage('Library item title cannot be empty.');

        new LibraryItemTitle($title);
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

    public function testInvalidLibraryItemIdIsRejected(): void
    {
        $this->expectException(InvalidLibraryItemException::class);
        $this->expectExceptionMessage('Library item id must be a valid UUID.');

        new LibraryItemId('not-a-uuid');
    }

    public function testReconstitutePreservesPersistedState(): void
    {
        $createdAt = new \DateTimeImmutable('2026-06-26 12:00:00');
        $id = LibraryItemId::generate();
        $contentId = ContentId::generate();
        $artifactId = ArtifactId::generate();
        $title = new LibraryItemTitle('Persisted quiz item');

        $item = LibraryItem::reconstitute(
            $id,
            $contentId,
            $artifactId,
            LibraryItemType::Quiz,
            $title,
            $createdAt,
        );

        self::assertSame($createdAt, $item->createdAt());
        self::assertSame(LibraryItemType::Quiz, $item->type());
        self::assertSame('Persisted quiz item', $item->title()->value);
    }
}
