<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Video;

use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoStatus;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'video_job')]
class VideoJobRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(name: 'original_filename', length: 255)]
    private string $originalFilename;

    #[ORM\Column(length: 32, enumType: VideoLanguage::class)]
    private VideoLanguage $language;

    #[ORM\Column(length: 32, enumType: VideoStatus::class)]
    private VideoStatus $status;

    #[ORM\Column(name: 'storage_path', length: 512)]
    private string $storagePath;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
    }

    public static function fromDomain(VideoJob $job): self
    {
        $record = new self();
        $record->syncFromDomain($job);
        $record->createdAt = $job->createdAt();

        return $record;
    }

    public function syncFromDomain(VideoJob $job): void
    {
        $this->id = $job->id()->value;
        $this->originalFilename = $job->originalFilename();
        $this->language = $job->language();
        $this->status = $job->status();
        $this->storagePath = $job->storagePath() ?? '';
    }

    public function toDomain(): VideoJob
    {
        return VideoJob::reconstitute(
            new VideoId($this->id),
            $this->originalFilename,
            $this->language,
            $this->status,
            $this->createdAt,
            $this->storagePath,
        );
    }
}
