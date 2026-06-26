<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Artifact;

use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'artifacts')]
class ArtifactRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(type: Types::GUID)]
    private string $contentId;

    #[ORM\Column(type: Types::GUID)]
    private string $processingJobId;

    #[ORM\Column(length: 32, enumType: ArtifactType::class)]
    private ArtifactType $type;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
    }

    public static function fromDomain(Artifact $artifact): self
    {
        $record = new self();
        $record->id = $artifact->id()->value;
        $record->contentId = $artifact->contentId()->value;
        $record->processingJobId = $artifact->processingJobId()->value;
        $record->type = $artifact->type();
        $record->content = $artifact->content()->value();
        $record->createdAt = $artifact->createdAt();

        return $record;
    }

    public function toDomain(): Artifact
    {
        return Artifact::reconstitute(
            new ArtifactId($this->id),
            new ContentId($this->contentId),
            new ProcessingJobId($this->processingJobId),
            $this->type,
            ArtifactContent::fromString($this->content),
            $this->createdAt,
        );
    }

    public function id(): string
    {
        return $this->id;
    }
}
