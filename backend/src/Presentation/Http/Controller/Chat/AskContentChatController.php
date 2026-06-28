<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Chat;

use App\Application\Chat\Commands\AskContentChatCommand;
use App\Application\Chat\Handlers\AskContentChatHandler;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Presentation\Http\Request\Chat\AskContentChatRequest;
use App\Presentation\Http\Request\Chat\Exception\InvalidChatRequestException;
use App\Presentation\Http\Response\Chat\ChatAnswerResponse;
use App\Presentation\OpenApi\Schema\ChatAnswer;
use App\Presentation\OpenApi\Schema\ChatRequest;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AskContentChatController extends AbstractController
{
    #[OA\Post(
        operationId: 'askContentChat',
        summary: 'Ask a question about content',
        description: 'Returns a chat answer with retrieved artifact sources for a content resource.',
        tags: ['Chat'],
        parameters: [
            new OA\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: '550e8400-e29b-41d4-a716-446655440000',
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ChatRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Chat answer with sources',
                content: new OA\JsonContent(ref: '#/components/schemas/ChatAnswer'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route(
        '/api/contents/{contentId}/chat',
        name: 'api_contents_chat',
        methods: ['POST'],
    )]
    public function __invoke(
        string $contentId,
        Request $request,
        AskContentChatHandler $handler,
    ): JsonResponse {
        try {
            new ContentId($contentId);
        } catch (InvalidContentIdException) {
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

        $result = $handler(new AskContentChatCommand(
            contentId: $contentId,
            question: $chatRequest->question,
        ));

        return $this->json(ChatAnswerResponse::fromResult($result));
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
