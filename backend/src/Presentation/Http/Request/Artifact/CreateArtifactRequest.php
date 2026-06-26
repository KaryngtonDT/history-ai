<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Artifact;

use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Domain\Processing\Exception\InvalidProcessingJobException;
use App\Domain\Processing\ProcessingJobId;
use App\Presentation\Http\Request\Artifact\Exception\InvalidArtifactRequestException;

final readonly class CreateArtifactRequest
{
    public function __construct(
        public string $contentId,
        public string $processingJobId,
        public ArtifactType $type,
        public string $content,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        if (!isset($payload['contentId']) || !is_string($payload['contentId'])) {
            throw new InvalidArtifactRequestException('Content id is required.');
        }

        if (!isset($payload['processingJobId']) || !is_string($payload['processingJobId'])) {
            throw new InvalidArtifactRequestException('Processing job id is required.');
        }

        if (!isset($payload['type']) || !is_string($payload['type'])) {
            throw new InvalidArtifactRequestException('Type is required.');
        }

        if (!isset($payload['content']) || !is_string($payload['content'])) {
            throw new InvalidArtifactRequestException('Content is required.');
        }

        try {
            new ContentId($payload['contentId']);
        } catch (InvalidContentIdException) {
            throw new InvalidArtifactRequestException('Content id is invalid.');
        }

        try {
            new ProcessingJobId($payload['processingJobId']);
        } catch (InvalidProcessingJobException) {
            throw new InvalidArtifactRequestException('Processing job id is invalid.');
        }

        try {
            $type = ArtifactType::from($payload['type']);
        } catch (\ValueError) {
            throw new InvalidArtifactRequestException('Type is invalid.');
        }

        if ('' === trim($payload['content'])) {
            throw new InvalidArtifactRequestException('Content cannot be empty.');
        }

        return new self(
            $payload['contentId'],
            $payload['processingJobId'],
            $type,
            $payload['content'],
        );
    }
}
