<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Content;

use App\Domain\Content\Content;
use App\Domain\Content\ContentId;
use App\Domain\Content\ContentSourceType;
use App\Domain\Content\ContentStatus;
use App\Domain\Content\ContentTitle;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'contents')]
class ContentRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 32, enumType: ContentSourceType::class)]
    private ContentSourceType $sourceType;

    #[ORM\Column(length: 32, enumType: ContentStatus::class)]
    private ContentStatus $status;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    private function __construct()
    {
    }

    public static function fromDomain(Content $content): self
    {
        $record = new self();
        $record->id = $content->id()->value;
        $record->title = $content->title()->value;
        $record->sourceType = $content->sourceType();
        $record->status = $content->status();
        $record->createdAt = $content->createdAt();
        $record->updatedAt = $content->updatedAt();

        return $record;
    }

    public function syncFromDomain(Content $content): void
    {
        $this->title = $content->title()->value;
        $this->sourceType = $content->sourceType();
        $this->status = $content->status();
        $this->updatedAt = $content->updatedAt();
    }

    public function toDomain(): Content
    {
        return Content::reconstitute(
            new ContentId($this->id),
            new ContentTitle($this->title),
            $this->sourceType,
            $this->status,
            $this->createdAt,
            $this->updatedAt,
        );
    }

    public function id(): string
    {
        return $this->id;
    }
}
