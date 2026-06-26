<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Content;

use App\Domain\Content\ContentSourceType;
use App\Presentation\Http\Request\Content\Exception\InvalidContentRequestException;

final readonly class CreateContentRequest
{
    public function __construct(
        public string $title,
        public ContentSourceType $sourceType,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        if (!isset($payload['title']) || !is_string($payload['title'])) {
            throw new InvalidContentRequestException('Title is required.');
        }

        if (!isset($payload['sourceType']) || !is_string($payload['sourceType'])) {
            throw new InvalidContentRequestException('Source type is required.');
        }

        try {
            $sourceType = ContentSourceType::from($payload['sourceType']);
        } catch (\ValueError) {
            throw new InvalidContentRequestException('Source type is invalid.');
        }

        return new self($payload['title'], $sourceType);
    }
}
