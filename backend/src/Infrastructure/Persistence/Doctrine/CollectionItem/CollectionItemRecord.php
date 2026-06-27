<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\CollectionItem;

use App\Domain\Collection\CollectionId;
use App\Domain\CollectionItem\CollectionItem;
use App\Domain\CollectionItem\CollectionItemId;
use App\Domain\CollectionItem\CollectionItemRepositoryInterface;
use App\Domain\Library\LibraryItemId;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'collection_items')]
#[ORM\UniqueConstraint(name: 'uniq_collection_items_collection_library_item', columns: ['collection_id', 'library_item_id'])]
#[ORM\Index(name: 'idx_collection_items_collection_id', columns: ['collection_id'])]
#[ORM\Index(name: 'idx_collection_items_library_item_id', columns: ['library_item_id'])]
class CollectionItemRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(type: Types::GUID)]
    private string $collectionId;

    #[ORM\Column(type: Types::GUID)]
    private string $libraryItemId;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
    }

    public static function fromDomain(CollectionItem $item): self
    {
        $record = new self();
        $record->id = $item->id()->value;
        $record->collectionId = $item->collectionId()->value;
        $record->libraryItemId = $item->libraryItemId()->value;
        $record->createdAt = $item->createdAt();

        return $record;
    }

    public function toDomain(): CollectionItem
    {
        return CollectionItem::reconstitute(
            new CollectionItemId($this->id),
            new CollectionId($this->collectionId),
            new LibraryItemId($this->libraryItemId),
            $this->createdAt,
        );
    }

    public function id(): string
    {
        return $this->id;
    }
}
