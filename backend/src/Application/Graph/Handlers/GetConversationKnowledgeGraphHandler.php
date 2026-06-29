<?php

declare(strict_types=1);

namespace App\Application\Graph\Handlers;

use App\Application\Graph\DTO\KnowledgeGraphResult;
use App\Application\Graph\Queries\GetConversationKnowledgeGraphQuery;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;
use App\Domain\Chat\Exception\ConversationNotFoundException;
use App\Domain\Graph\KnowledgeGraphBuilder;
use App\Domain\Relation\ArtifactRelationResolver;

final class GetConversationKnowledgeGraphHandler
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository,
        private readonly ArtifactRepositoryInterface $artifactRepository,
    ) {
    }

    public function __invoke(GetConversationKnowledgeGraphQuery $query): KnowledgeGraphResult
    {
        $conversation = $this->conversationRepository->findById(
            new ConversationId($query->conversationId),
        );

        if (null === $conversation) {
            throw new ConversationNotFoundException(
                sprintf('Conversation "%s" was not found.', $query->conversationId),
            );
        }

        $artifacts = $this->loadArtifactsInSelectedDocumentOrder($conversation);
        $relations = ArtifactRelationResolver::resolve($artifacts);
        $graph = KnowledgeGraphBuilder::build($artifacts, $relations);

        return KnowledgeGraphResult::fromDomain($graph);
    }

    /**
     * @return list<Artifact>
     */
    private function loadArtifactsInSelectedDocumentOrder(Conversation $conversation): array
    {
        $artifacts = [];

        foreach ($conversation->documents()->all() as $selectedDocument) {
            foreach ($this->artifactRepository->findByContentId($selectedDocument->contentId()) as $artifact) {
                $artifacts[] = $artifact;
            }
        }

        return $artifacts;
    }
}
