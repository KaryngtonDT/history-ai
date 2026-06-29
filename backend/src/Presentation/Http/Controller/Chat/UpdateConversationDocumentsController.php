<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Chat;

use App\Application\Chat\Commands\UpdateConversationDocumentsCommand;
use App\Application\Chat\Handlers\UpdateConversationDocumentsHandler;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\Exception\ConversationNotFoundException;
use App\Domain\Chat\Exception\InvalidConversationIdException;
use App\Presentation\Http\Request\Chat\Exception\InvalidUpdateConversationDocumentsRequestException;
use App\Presentation\Http\Request\Chat\UpdateConversationDocumentsRequest;
use App\Presentation\Http\Response\Chat\ConversationResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateConversationDocumentsController extends AbstractController
{
    #[OA\Put(
        operationId: 'updateConversationDocuments',
        summary: 'Replace selected documents in a conversation',
        description: 'Replaces the conversation document selection with the provided content ids. Messages are preserved. At least one document is required. Duplicate ids are deduplicated server-side while preserving order.',
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(
                name: 'conversationId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: '550e8400-e29b-41d4-a716-446655440001',
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateConversationDocumentsRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated conversation',
                content: new OA\JsonContent(ref: '#/components/schemas/ConversationResponse'),
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
        '/api/conversations/{conversationId}/documents',
        name: 'api_conversation_documents_update',
        methods: ['PUT'],
    )]
    public function __invoke(
        string $conversationId,
        Request $request,
        UpdateConversationDocumentsHandler $handler,
    ): JsonResponse {
        try {
            new ConversationId($conversationId);
        } catch (InvalidConversationIdException) {
            return $this->invalidRequestResponse();
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->invalidRequestResponse();
        }

        try {
            $updateRequest = UpdateConversationDocumentsRequest::fromArray($payload);
        } catch (InvalidUpdateConversationDocumentsRequestException) {
            return $this->invalidRequestResponse();
        }

        try {
            $result = $handler(new UpdateConversationDocumentsCommand(
                conversationId: $conversationId,
                contentIds: $updateRequest->contentIds,
            ));
        } catch (ConversationNotFoundException) {
            return $this->json(
                ['error' => 'Conversation not found'],
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->json(ConversationResponse::fromResult($result));
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
