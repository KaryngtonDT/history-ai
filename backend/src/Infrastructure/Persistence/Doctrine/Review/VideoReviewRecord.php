<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Review;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'video_reviews')]
class VideoReviewRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(name: 'video_id', type: Types::GUID)]
    private string $videoId;

    #[ORM\Column(name: 'execution_version_number', type: Types::INTEGER)]
    private int $executionVersionNumber;

    /** @var array<string, int> */
    #[ORM\Column(type: Types::JSON)]
    private array $scores = [];

    #[ORM\Column(type: Types::TEXT)]
    private string $comment = '';

    #[ORM\Column(name: 'created_at', type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

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
        $record->videoId = (string) $payload['videoId'];
        $record->executionVersionNumber = (int) $payload['executionVersionNumber'];
        $record->scores = (array) $payload['scores'];
        $record->comment = (string) ($payload['comment'] ?? '');
        $record->createdAt = new \DateTimeImmutable((string) $payload['createdAt']);

        return $record;
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'videoId' => $this->videoId,
            'executionVersionNumber' => $this->executionVersionNumber,
            'scores' => $this->scores,
            'comment' => $this->comment,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
