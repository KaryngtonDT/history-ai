<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Library;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Content\ContentId;
use App\Domain\Library\LibraryItem;
use App\Domain\Library\LibraryItemId;
use App\Domain\Library\LibraryItemTitle;
use App\Domain\Library\LibraryItemType;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'library_items')]
class LibraryItemRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(type: Types::GUID)]
    private string $contentId;

    #[ORM\Column(type: Types::GUID)]
    private string $artifactId;

    #[ORM\Column(length: 32, enumType: LibraryItemType::class)]
    private LibraryItemType $type;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
    }

    public static function fromDomain(LibraryItem $item): self
    {
        $record = new self();
        $record->id = $item->id()->value;
        $record->contentId = $item->contentId()->value;
        $record->artifactId = $item->artifactId()->value;
        $record->type = $item->type();
        $record->title = $item->title()->value;
        $record->createdAt = $item->createdAt();

        return $record;
    }

    public function toDomain(): LibraryItem
    {
        return LibraryItem::reconstitute(
            new LibraryItemId($this->id),
            new ContentId($this->contentId),
            new ArtifactId($this->artifactId),
            $this->type,
            new LibraryItemTitle($this->title),
            $this->createdAt,
        );
    }

    public function id(): string
    {
        return $this->id;
    }
}
