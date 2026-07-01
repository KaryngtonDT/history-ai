<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Telemetry;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pipeline_telemetry')]
#[ORM\Index(name: 'idx_pipeline_telemetry_workspace', columns: ['workspace_id'])]
class PipelineTelemetryRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(name: 'workspace_id', type: Types::GUID)]
    private string $workspaceId;

    /** @var array<string, mixed> */
    #[ORM\Column(type: Types::JSON)]
    private array $payload;

    #[ORM\Column(name: 'recorded_at', type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $recordedAt;

    private function __construct()
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        $record = new self();
        $record->id = (string) $payload['id'];
        $record->workspaceId = (string) $payload['workspaceId'];
        $record->payload = $payload;
        $record->recordedAt = new \DateTimeImmutable((string) ($payload['recordedAt'] ?? 'now'));

        return $record;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
