<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Shadow;

use App\Application\Shadow\Handlers\CheckShadowInterventionHandler;
use App\Application\Shadow\Queries\CheckShadowInterventionQuery;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Presentation\Http\Response\Shadow\ShadowInterventionCheckResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CheckShadowInterventionController extends AbstractController
{
    #[OA\Get(
        operationId: 'checkShadowIntervention',
        summary: 'Check whether Shadow should intervene during playback',
        tags: ['Shadow'],
        parameters: [
            new OA\Parameter(name: 'videoId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'sessionId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'time', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Intervention check result'),
            new OA\Response(response: 400, description: 'Invalid request'),
        ],
    )]
    #[Route(
        '/api/videos/{videoId}/shadow/sessions/{sessionId}/intervention',
        name: 'api_videos_shadow_sessions_intervention_check',
        methods: ['GET'],
    )]
    public function __invoke(
        string $videoId,
        string $sessionId,
        Request $request,
        CheckShadowInterventionHandler $handler,
    ): JsonResponse {
        try {
            new VideoId($videoId);
        } catch (InvalidVideoIdException) {
            return $this->invalidRequestResponse();
        }

        $time = $request->query->get('time');

        if (!is_numeric($time)) {
            return $this->invalidRequestResponse();
        }

        try {
            $result = $handler(new CheckShadowInterventionQuery(
                videoId: $videoId,
                sessionId: $sessionId,
                currentTimeSeconds: (float) $time,
            ));
        } catch (InvalidShadowSessionException) {
            return $this->invalidRequestResponse();
        }

        return $this->json(ShadowInterventionCheckResponse::fromResult($result)->toArray());
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
