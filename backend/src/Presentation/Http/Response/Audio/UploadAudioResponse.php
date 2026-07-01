<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Audio;

use App\Application\AudioUpload\DTO\GetAudioResult;
use App\Application\AudioUpload\DTO\UploadAudioResult;

final readonly class UploadAudioResponse
{
    public function __construct(
        public string $audioId,
        public string $status,
    ) {
    }

    public static function fromResult(UploadAudioResult $result): self
    {
        return new self(
            audioId: $result->audioId->value,
            status: $result->status->value,
        );
    }

    /**
     * @return array{audioId: string, status: string}
     */
    public function toArray(): array
    {
        return [
            'audioId' => $this->audioId,
            'status' => $this->status,
        ];
    }
}
