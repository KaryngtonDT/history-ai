<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\TTS;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'video_audio')]
class AudioRecord
{
    #[ORM\Id]
    #[ORM\Column(name: 'video_id', type: 'string', length: 36)]
    private string $videoId;

    #[ORM\Id]
    #[ORM\Column(name: 'target_language', type: 'string', length: 32)]
    private string $targetLanguage;

    #[ORM\Column(name: 'payload', type: 'text')]
    private string $payload;

    public function __construct(string $videoId, string $targetLanguage, string $payload)
    {
        $this->videoId = $videoId;
        $this->targetLanguage = $targetLanguage;
        $this->payload = $payload;
    }

    public function videoId(): string
    {
        return $this->videoId;
    }

    public function targetLanguage(): string
    {
        return $this->targetLanguage;
    }

    public function payload(): string
    {
        return $this->payload;
    }

    public function syncPayload(string $payload): void
    {
        $this->payload = $payload;
    }
}
