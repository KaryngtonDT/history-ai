<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Source;

use App\Domain\Source\Source;
use App\Domain\Source\SourceId;
use App\Domain\Source\SourceMetadata;
use App\Domain\Source\SourceStatus;
use App\Domain\Source\SourceType;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'source')]
class SourceRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(length: 32, enumType: SourceType::class)]
    private SourceType $type;

    #[ORM\Column(name: 'original_filename', length: 255)]
    private string $originalFilename;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $language = null;

    #[ORM\Column(length: 32, enumType: SourceStatus::class)]
    private SourceStatus $status;

    #[ORM\Column(name: 'storage_path', length: 512)]
    private string $storagePath;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
    }

    public static function fromDomain(Source $source): self
    {
        $record = new self();
        $record->syncFromDomain($source);
        $record->createdAt = $source->createdAt();

        return $record;
    }

    public function syncFromDomain(Source $source): void
    {
        $this->id = $source->id()->value;
        $this->type = $source->type();
        $this->originalFilename = $source->metadata()->originalFilename;
        $this->title = $source->metadata()->title;
        $this->language = $source->metadata()->language;
        $this->status = $source->status();
        $this->storagePath = $source->storagePath() ?? '';
    }

    public function toDomain(): Source
    {
        return Source::reconstitute(
            new SourceId($this->id),
            $this->type,
            new SourceMetadata(
                $this->originalFilename,
                $this->title,
                $this->language,
            ),
            $this->status,
            $this->createdAt,
            $this->storagePath,
        );
    }
}
