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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AskConversationChatStreamController extends AbstractController
{
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
