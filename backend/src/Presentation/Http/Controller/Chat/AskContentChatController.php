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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AskContentChatController extends AbstractController
{
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
