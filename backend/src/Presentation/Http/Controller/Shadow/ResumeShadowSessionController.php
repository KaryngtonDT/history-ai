<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Shadow;

use App\Application\Shadow\Commands\ResumeShadowSessionCommand;
use App\Application\Shadow\Handlers\ResumeShadowSessionHandler;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Presentation\Http\Response\Shadow\ShadowSessionResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ResumeShadowSessionController extends AbstractController
{
    #[OA\Post(
        operationId: 'resumeShadowSession',
        summary: 'Resume a Shadow watch session',
        tags: ['Shadow'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Session resumed',
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
        '/api/videos/{videoId}/shadow/sessions/{sessionId}/resume',
        name: 'api_videos_shadow_sessions_resume',
        methods: ['POST'],
    )]
    public function __invoke(
        string $videoId,
        string $sessionId,
        Request $request,
        ResumeShadowSessionHandler $handler,
    ): JsonResponse {
        try {
            new VideoId($videoId);
        } catch (InvalidVideoIdException) {
            return $this->invalidRequestResponse();
        }

        $currentTimeSeconds = $this->optionalTime($request);

        try {
            $result = $handler(new ResumeShadowSessionCommand(
                videoId: $videoId,
                sessionId: $sessionId,
                currentTimeSeconds: $currentTimeSeconds,
            ));
        } catch (InvalidShadowSessionException) {
            return $this->invalidRequestResponse();
        }

        return $this->json(ShadowSessionResponse::fromResult($result)->toArray());
    }

    private function optionalTime(Request $request): ?float
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload) || !isset($payload['time']) || !is_numeric($payload['time'])) {
            return null;
        }

        return (float) $payload['time'];
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
