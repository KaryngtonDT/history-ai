<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Audio;

use App\Application\AudioUpload\DTO\GetAudioResult;

final readonly class GetAudioResponse
{
    public function __construct(
        public string $audioId,
        public string $title,
        public string $originalFilename,
        public string $status,
        public string $type,
        public string $createdAt,
    ) {
    }

    public static function fromResult(GetAudioResult $result): self
    {
        return new self(
            audioId: $result->audioId->value,
            title: $result->title,
            originalFilename: $result->originalFilename,
            status: $result->status->value,
            type: $result->type->value,
            createdAt: $result->createdAt,
        );
    }

    /**
     * @return array{
     *     audioId: string,
     *     title: string,
     *     originalFilename: string,
     *     status: string,
     *     type: string,
     *     createdAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'audioId' => $this->audioId,
            'title' => $this->title,
            'originalFilename' => $this->originalFilename,
            'status' => $this->status,
            'type' => $this->type,
            'createdAt' => $this->createdAt,
        ];
    }
}
