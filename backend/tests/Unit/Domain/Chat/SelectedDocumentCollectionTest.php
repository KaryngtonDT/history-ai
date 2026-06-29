<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\Exception\InvalidConversationDocumentException;
use App\Domain\Chat\SelectedDocument;
use App\Domain\Chat\SelectedDocumentCollection;
use App\Domain\Content\ContentId;
use PHPUnit\Framework\TestCase;

final class SelectedDocumentCollectionTest extends TestCase
{
    private const string CONTENT_ID_A = '550e8400-e29b-41d4-a716-446655440000';
    private const string CONTENT_ID_B = '550e8400-e29b-41d4-a716-446655440001';
    private const string CONTENT_ID_C = '550e8400-e29b-41d4-a716-446655440002';

    public function testEmptyCollectionHasNoDocuments(): void
    {
        $collection = SelectedDocumentCollection::empty();

        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->all());
    }

    public function testFromContentIdCreatesSingleDocumentCollection(): void
    {
        $collection = SelectedDocumentCollection::fromContentId(new ContentId(self::CONTENT_ID_A));

        self::assertSame(1, $collection->count());
        self::assertTrue($collection->contains(new ContentId(self::CONTENT_ID_A)));
    }

    public function testAddAppendsDocumentAndPreservesOrder(): void
    {
        $collection = SelectedDocumentCollection::fromContentId(new ContentId(self::CONTENT_ID_A))
            ->add(new SelectedDocument(new ContentId(self::CONTENT_ID_B)))
            ->add(new SelectedDocument(new ContentId(self::CONTENT_ID_C)));

        self::assertSame(3, $collection->count());
        self::assertSame(
            [self::CONTENT_ID_A, self::CONTENT_ID_B, self::CONTENT_ID_C],
            array_map(
                static fn (SelectedDocument $document): string => $document->contentId()->value,
                $collection->all(),
            ),
        );
    }

    public function testAddIgnoresDuplicateDocuments(): void
    {
        $original = SelectedDocumentCollection::fromContentId(new ContentId(self::CONTENT_ID_A));
        $updated = $original->add(new SelectedDocument(new ContentId(self::CONTENT_ID_A)));

        self::assertSame(1, $updated->count());
        self::assertSame($original, $updated);
    }

    public function testConstructorDeduplicatesWhilePreservingFirstOccurrenceOrder(): void
    {
        $collection = new SelectedDocumentCollection([
            new SelectedDocument(new ContentId(self::CONTENT_ID_A)),
            new SelectedDocument(new ContentId(self::CONTENT_ID_B)),
            new SelectedDocument(new ContentId(self::CONTENT_ID_A)),
            new SelectedDocument(new ContentId(self::CONTENT_ID_C)),
        ]);

        self::assertSame(3, $collection->count());
        self::assertSame(
            [self::CONTENT_ID_A, self::CONTENT_ID_B, self::CONTENT_ID_C],
            array_map(
                static fn (SelectedDocument $document): string => $document->contentId()->value,
                $collection->all(),
            ),
        );
    }

    public function testRemoveDeletesDocumentAndPreservesOrder(): void
    {
        $collection = new SelectedDocumentCollection([
            new SelectedDocument(new ContentId(self::CONTENT_ID_A)),
            new SelectedDocument(new ContentId(self::CONTENT_ID_B)),
            new SelectedDocument(new ContentId(self::CONTENT_ID_C)),
        ])->remove(new ContentId(self::CONTENT_ID_B));

        self::assertSame(2, $collection->count());
        self::assertFalse($collection->contains(new ContentId(self::CONTENT_ID_B)));
        self::assertSame(
            [self::CONTENT_ID_A, self::CONTENT_ID_C],
            array_map(
                static fn (SelectedDocument $document): string => $document->contentId()->value,
                $collection->all(),
            ),
        );
    }

    public function testRemoveUnknownDocumentReturnsEquivalentCollection(): void
    {
        $collection = SelectedDocumentCollection::fromContentId(new ContentId(self::CONTENT_ID_A));
        $updated = $collection->remove(new ContentId(self::CONTENT_ID_B));

        self::assertSame(1, $updated->count());
        self::assertTrue($updated->contains(new ContentId(self::CONTENT_ID_A)));
    }

    public function testRemoveLastDocumentThrows(): void
    {
        $collection = SelectedDocumentCollection::fromContentId(new ContentId(self::CONTENT_ID_A));

        $this->expectException(InvalidConversationDocumentException::class);

        $collection->remove(new ContentId(self::CONTENT_ID_A));
    }

    public function testOriginalCollectionIsUnchangedAfterAddAndRemove(): void
    {
        $original = SelectedDocumentCollection::fromContentId(new ContentId(self::CONTENT_ID_A))
            ->add(new SelectedDocument(new ContentId(self::CONTENT_ID_B)));

        $original->remove(new ContentId(self::CONTENT_ID_B));
        $original->add(new SelectedDocument(new ContentId(self::CONTENT_ID_C)));

        self::assertSame(2, $original->count());
        self::assertTrue($original->contains(new ContentId(self::CONTENT_ID_A)));
        self::assertTrue($original->contains(new ContentId(self::CONTENT_ID_B)));
        self::assertFalse($original->contains(new ContentId(self::CONTENT_ID_C)));
    }
}
