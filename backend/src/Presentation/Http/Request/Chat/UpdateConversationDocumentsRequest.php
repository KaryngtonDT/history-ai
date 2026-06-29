<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Chat;

use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Presentation\Http\Request\Chat\Exception\InvalidUpdateConversationDocumentsRequestException;

final readonly class UpdateConversationDocumentsRequest
{
    /**
     * @param list<string> $contentIds
     */
    public function __construct(public array $contentIds)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        if (!isset($payload['contentIds'])) {
            throw new InvalidUpdateConversationDocumentsRequestException('Content ids are required.');
        }

        if (!is_array($payload['contentIds'])) {
            throw new InvalidUpdateConversationDocumentsRequestException('Content ids are required.');
        }

        if ([] === $payload['contentIds']) {
            throw new InvalidUpdateConversationDocumentsRequestException(
                'A conversation must contain at least one document.',
            );
        }

        /** @var list<string> $contentIds */
        $contentIds = [];

        foreach ($payload['contentIds'] as $contentId) {
            if (!is_string($contentId)) {
                throw new InvalidUpdateConversationDocumentsRequestException('Content id is invalid.');
            }

            try {
                new ContentId($contentId);
            } catch (InvalidContentIdException) {
                throw new InvalidUpdateConversationDocumentsRequestException('Content id is invalid.');
            }

            $contentIds[] = $contentId;
        }

        return new self($contentIds);
    }
}
