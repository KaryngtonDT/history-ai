<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Shadow;

use App\Application\Shadow\Commands\AskShadowQuestionCommand;
use App\Application\Shadow\Handlers\AskShadowQuestionHandler;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Presentation\Http\Request\Shadow\AskShadowQuestionRequest;
use App\Presentation\Http\Request\Shadow\Exception\InvalidShadowRequestException;
use App\Presentation\Http\Response\Shadow\ShadowAnswerResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AskShadowQuestionController extends AbstractController
{
    #[OA\Post(
        operationId: 'askShadowQuestion',
        summary: 'Ask Shadow a contextual watch question',
        tags: ['Shadow'],
    )]
    #[Route(
        '/api/videos/{videoId}/shadow/sessions/{sessionId}/ask',
        name: 'api_videos_shadow_sessions_ask',
        methods: ['POST'],
    )]
    public function __invoke(
        string $videoId,
        string $sessionId,
        Request $request,
        AskShadowQuestionHandler $handler,
    ): JsonResponse {
        try {
            new VideoId($videoId);
        } catch (InvalidVideoIdException) {
            return $this->invalidRequestResponse();
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->invalidRequestResponse();
        }

        try {
            $questionRequest = AskShadowQuestionRequest::fromArray($payload);
        } catch (InvalidShadowRequestException) {
            return $this->invalidRequestResponse();
        }

        try {
            $result = $handler(new AskShadowQuestionCommand(
                videoId: $videoId,
                sessionId: $sessionId,
                question: $questionRequest->question,
                currentTimeSeconds: $questionRequest->time,
            ));
        } catch (InvalidShadowSessionException) {
            return $this->invalidRequestResponse();
        }

        return $this->json(ShadowAnswerResponse::fromResult($result)->toArray());
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
