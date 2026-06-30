<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Workspace;

use App\Domain\Workspace\Project;
use App\Domain\Workspace\ProjectId;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'workspace_projects')]
#[ORM\Index(name: 'idx_workspace_projects_name', columns: ['name'])]
class ProjectRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::JSON)]
    private array $videos = [];

    private function __construct()
    {
    }

    public static function fromDomain(Project $project): self
    {
        $record = new self();
        $record->id = $project->id()->value;
        $record->name = $project->name();
        $record->createdAt = $project->createdAt();
        $record->videos = (new ProjectVideoJsonMapper())->toArray($project->videos());

        return $record;
    }

    public function updateFromDomain(Project $project): void
    {
        $this->name = $project->name();
        $this->videos = (new ProjectVideoJsonMapper())->toArray($project->videos());
    }

    public function toDomain(): Project
    {
        return Project::reconstitute(
            new ProjectId($this->id),
            $this->name,
            $this->createdAt,
            (new ProjectVideoJsonMapper())->fromJson(json_encode($this->videos, JSON_THROW_ON_ERROR)),
        );
    }

    public function id(): string
    {
        return $this->id;
    }
}
