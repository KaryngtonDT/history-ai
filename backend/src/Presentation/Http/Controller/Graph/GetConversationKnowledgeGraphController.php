<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Graph;

use App\Application\Graph\Handlers\GetConversationKnowledgeGraphHandler;
use App\Application\Graph\Queries\GetConversationKnowledgeGraphQuery;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\Exception\ConversationNotFoundException;
use App\Domain\Chat\Exception\InvalidConversationIdException;
use App\Presentation\Http\Response\Graph\KnowledgeGraphResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetConversationKnowledgeGraphController extends AbstractController
{
    #[Route(
        '/api/conversations/{conversationId}/graph',
        name: 'api_conversation_graph_get',
        methods: ['GET'],
    )]
    public function __invoke(
        string $conversationId,
        GetConversationKnowledgeGraphHandler $handler,
    ): JsonResponse {
        try {
            new ConversationId($conversationId);
        } catch (InvalidConversationIdException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $handler(new GetConversationKnowledgeGraphQuery($conversationId));
        } catch (ConversationNotFoundException) {
            return $this->json(
                ['error' => 'Conversation not found'],
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->json(KnowledgeGraphResponse::fromResult($result));
    }
}
