<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Pipeline;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pipeline_configuration')]
class PipelineConfigurationRecord
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(name: 'version', type: 'integer')]
    private int $version;

    #[ORM\Column(name: 'payload', type: 'text')]
    private string $payload;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        int $version,
        string $payload,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ) {
        $this->id = $id;
        $this->version = $version;
        $this->payload = $payload;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function payload(): string
    {
        return $this->payload;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
