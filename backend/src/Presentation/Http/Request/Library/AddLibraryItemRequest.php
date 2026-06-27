<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Library;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\Exception\InvalidArtifactException;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Domain\Library\LibraryItemType;
use App\Presentation\Http\Request\Library\Exception\InvalidLibraryRequestException;

final readonly class AddLibraryItemRequest
{
    public function __construct(
        public string $contentId,
        public string $artifactId,
        public LibraryItemType $type,
        public string $title,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        if (!isset($payload['contentId']) || !is_string($payload['contentId'])) {
            throw new InvalidLibraryRequestException('Content id is required.');
        }

        if (!isset($payload['artifactId']) || !is_string($payload['artifactId'])) {
            throw new InvalidLibraryRequestException('Artifact id is required.');
        }

        if (!isset($payload['type']) || !is_string($payload['type'])) {
            throw new InvalidLibraryRequestException('Type is required.');
        }

        if (!isset($payload['title']) || !is_string($payload['title'])) {
            throw new InvalidLibraryRequestException('Title is required.');
        }

        try {
            new ContentId($payload['contentId']);
        } catch (InvalidContentIdException) {
            throw new InvalidLibraryRequestException('Content id is invalid.');
        }

        try {
            new ArtifactId($payload['artifactId']);
        } catch (InvalidArtifactException) {
            throw new InvalidLibraryRequestException('Artifact id is invalid.');
        }

        try {
            $type = LibraryItemType::from($payload['type']);
        } catch (\ValueError) {
            throw new InvalidLibraryRequestException('Type is invalid.');
        }

        if ('' === trim($payload['title'])) {
            throw new InvalidLibraryRequestException('Title cannot be empty.');
        }

        return new self(
            $payload['contentId'],
            $payload['artifactId'],
            $type,
            $payload['title'],
        );
    }
}
