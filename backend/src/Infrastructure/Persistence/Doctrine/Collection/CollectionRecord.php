<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Collection;

use App\Domain\Collection\Collection;
use App\Domain\Collection\CollectionDescription;
use App\Domain\Collection\CollectionId;
use App\Domain\Collection\CollectionName;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'collections')]
#[ORM\Index(name: 'idx_collections_name', columns: ['name'])]
class CollectionRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
    }

    public static function fromDomain(Collection $collection): self
    {
        $record = new self();
        $record->id = $collection->id()->value;
        $record->name = $collection->name()->value;
        $record->description = $collection->description()->value;
        $record->createdAt = $collection->createdAt();

        return $record;
    }

    public function toDomain(): Collection
    {
        return Collection::reconstitute(
            new CollectionId($this->id),
            new CollectionName($this->name),
            new CollectionDescription($this->description),
            $this->createdAt,
        );
    }

    public function id(): string
    {
        return $this->id;
    }
}
