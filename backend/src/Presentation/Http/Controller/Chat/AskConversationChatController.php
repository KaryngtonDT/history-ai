<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Chat;

use App\Application\Chat\Commands\AskConversationChatCommand;
use App\Application\Chat\Handlers\AskConversationChatHandler;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\Exception\ConversationContentMismatchException;
use App\Domain\Chat\Exception\InvalidConversationIdException;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Presentation\Http\Request\Chat\AskContentChatRequest;
use App\Presentation\Http\Request\Chat\Exception\InvalidChatRequestException;
use App\Presentation\Http\Response\Chat\ConversationChatResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AskConversationChatController extends AbstractController
{
    #[OA\Post(
        operationId: 'askConversationChat',
        summary: 'Ask a question in a persistent conversation',
        description: 'Appends the user question, generates an answer via RAG, persists the conversation, and returns the full message history with answer metadata.',
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: '550e8400-e29b-41d4-a716-446655440000',
            ),
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
            content: new OA\JsonContent(ref: '#/components/schemas/ChatRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated conversation with chat answer',
                content: new OA\JsonContent(ref: '#/components/schemas/ConversationChatResponse'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route(
        '/api/contents/{contentId}/conversations/{conversationId}/chat',
        name: 'api_contents_conversation_chat',
        methods: ['POST'],
    )]
    public function __invoke(
        string $contentId,
        string $conversationId,
        Request $request,
        AskConversationChatHandler $handler,
    ): JsonResponse {
        try {
            new ContentId($contentId);
        } catch (InvalidContentIdException) {
            return $this->invalidRequestResponse();
        }

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
            $chatRequest = AskContentChatRequest::fromArray($payload);
        } catch (InvalidChatRequestException) {
            return $this->invalidRequestResponse();
        }

        try {
            $result = $handler(new AskConversationChatCommand(
                contentId: $contentId,
                conversationId: $conversationId,
                question: $chatRequest->question,
            ));
        } catch (ConversationContentMismatchException) {
            return $this->invalidRequestResponse();
        }

        return $this->json(ConversationChatResponse::fromResult($result));
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
