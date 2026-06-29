<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Graph;

use App\Application\Graph\Handlers\GetConversationKnowledgeGraphHandler;
use App\Application\Graph\Queries\GetConversationKnowledgeGraphQuery;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\Exception\ConversationNotFoundException;
use App\Domain\Chat\Exception\InvalidConversationIdException;
use App\Presentation\Http\Response\Graph\KnowledgeGraphResponse;
use App\Presentation\OpenApi\Schema\KnowledgeGraph;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetConversationKnowledgeGraphController extends AbstractController
{
    #[OA\Get(
        operationId: 'getConversationKnowledgeGraph',
        summary: 'Get knowledge graph for a conversation',
        description: 'Returns artifact nodes and relation edges scoped to the documents selected in a persistent conversation.',
        tags: ['Graph'],
        parameters: [
            new OA\Parameter(
                name: 'conversationId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: '550e8400-e29b-41d4-a716-446655440001',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Conversation-scoped knowledge graph projection',
                content: new OA\JsonContent(ref: '#/components/schemas/KnowledgeGraph'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'Conversation not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
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
