<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Shadow;

use App\Application\Shadow\Commands\StartShadowSessionCommand;
use App\Application\Shadow\Handlers\StartShadowSessionHandler;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Presentation\Http\Request\Shadow\Exception\InvalidShadowRequestException;
use App\Presentation\Http\Request\Shadow\StartShadowSessionRequest;
use App\Presentation\Http\Response\Shadow\ShadowSessionResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StartShadowSessionController extends AbstractController
{
    #[OA\Post(
        operationId: 'startShadowSession',
        summary: 'Start a Shadow watch session',
        tags: ['Shadow'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StartShadowSessionRequest'),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Session started',
                content: new OA\JsonContent(ref: '#/components/schemas/ShadowSession'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route(
        '/api/videos/{videoId}/shadow/sessions',
        name: 'api_videos_shadow_sessions_start',
        methods: ['POST'],
    )]
    public function __invoke(
        string $videoId,
        Request $request,
        StartShadowSessionHandler $handler,
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
            $sessionRequest = StartShadowSessionRequest::fromArray($payload);
        } catch (InvalidShadowRequestException) {
            return $this->invalidRequestResponse();
        }

        try {
            $result = $handler(new StartShadowSessionCommand(
                videoId: $videoId,
                targetLanguage: $sessionRequest->targetLanguage,
                contentId: $sessionRequest->contentId,
                conversationId: $sessionRequest->conversationId,
            ));
        } catch (InvalidShadowSessionException) {
            return $this->invalidRequestResponse();
        }

        return $this->json(ShadowSessionResponse::fromResult($result)->toArray(), Response::HTTP_CREATED);
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
