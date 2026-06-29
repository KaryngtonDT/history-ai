<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Chat;

use App\Application\Chat\Commands\AskConversationChatStreamCommand;
use App\Application\Chat\Handlers\AskConversationChatStreamHandler;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\Exception\ConversationContentMismatchException;
use App\Domain\Chat\Exception\InvalidConversationIdException;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Presentation\Http\Request\Chat\AskContentChatRequest;
use App\Presentation\Http\Request\Chat\Exception\InvalidChatRequestException;
use App\Presentation\Http\Response\Chat\ConversationChatStreamResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AskConversationChatStreamController extends AbstractController
{
    #[OA\Post(
        operationId: 'askConversationChatStream',
        summary: 'Stream a chat answer in a persistent conversation',
        description: 'Returns a Server-Sent Events stream of answer tokens. Each `token` event carries a `ChatStreamToken` JSON payload (`index`, `text`). A `conversation` event carries the persisted `ConversationStreamEvent` payload (`conversation` with `id`, `contentId`, `messages[]`, `documents[]`). A final `done` event signals completion.',
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
                description: 'SSE stream of chat tokens, persisted conversation, and done event',
                content: new OA\MediaType(
                    mediaType: 'text/event-stream',
                    schema: new OA\Schema(
                        type: 'string',
                        description: 'SSE events: `token` (data: ChatStreamToken JSON), `conversation` (data: ConversationStreamEvent JSON), and `done` (data: {})',
                        example: "event: token\ndata: {\"index\":0,\"text\":\"Mock \"}\n\nevent: token\ndata: {\"index\":1,\"text\":\"answer \"}\n\nevent: conversation\ndata: {\"conversation\":{\"id\":\"550e8400-e29b-41d4-a716-446655440001\",\"contentId\":\"550e8400-e29b-41d4-a716-446655440000\",\"messages\":[],\"documents\":[{\"contentId\":\"550e8400-e29b-41d4-a716-446655440000\"}]}}\n\nevent: done\ndata: {}\n\n",
                    ),
                ),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route(
        '/api/contents/{contentId}/conversations/{conversationId}/chat/stream',
        name: 'api_contents_conversation_chat_stream',
        methods: ['POST'],
    )]
    public function __invoke(
        string $contentId,
        string $conversationId,
        Request $request,
        AskConversationChatStreamHandler $handler,
    ): Response {
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
            $result = $handler(new AskConversationChatStreamCommand(
                contentId: $contentId,
                conversationId: $conversationId,
                question: $chatRequest->question,
            ));
        } catch (ConversationContentMismatchException) {
            return $this->invalidRequestResponse();
        }

        return ConversationChatStreamResponse::fromResult($result);
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
