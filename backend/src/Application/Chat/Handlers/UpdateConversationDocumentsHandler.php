<?php

declare(strict_types=1);

namespace App\Application\Chat\Handlers;

use App\Application\Chat\Commands\UpdateConversationDocumentsCommand;
use App\Application\Chat\DTO\ConversationResult;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;
use App\Domain\Chat\Exception\ConversationNotFoundException;
use App\Domain\Chat\Exception\InvalidConversationDocumentException;
use App\Domain\Chat\SelectedDocument;
use App\Domain\Chat\SelectedDocumentCollection;
use App\Domain\Content\ContentId;

final class UpdateConversationDocumentsHandler
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository,
    ) {
    }

    public function __invoke(UpdateConversationDocumentsCommand $command): ConversationResult
    {
        $conversationId = new ConversationId($command->conversationId);
        $conversation = $this->conversationRepository->findById($conversationId);

        if (null === $conversation) {
            throw new ConversationNotFoundException(
                sprintf('Conversation "%s" was not found.', $command->conversationId),
            );
        }

        $documents = $this->buildDocumentCollection($command->contentIds);
        $updated = $conversation->withDocuments($documents);

        $this->conversationRepository->save($updated);

        return ConversationResult::fromDomain($updated);
    }

    /**
     * @param list<string> $contentIds
     */
    private function buildDocumentCollection(array $contentIds): SelectedDocumentCollection
    {
        if ([] === $contentIds) {
            throw new InvalidConversationDocumentException(
                'A conversation must contain at least one document.',
            );
        }

        /** @var list<SelectedDocument> $documents */
        $documents = [];

        foreach ($contentIds as $contentIdValue) {
            $documents[] = new SelectedDocument(new ContentId($contentIdValue));
        }

        return new SelectedDocumentCollection($documents);
    }
}
