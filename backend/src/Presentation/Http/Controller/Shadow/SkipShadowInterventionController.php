<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Shadow;

use App\Application\Shadow\Commands\SkipShadowInterventionCommand;
use App\Application\Shadow\Handlers\SkipShadowInterventionHandler;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Presentation\Http\Request\Shadow\Exception\InvalidShadowRequestException;
use App\Presentation\Http\Request\Shadow\SkipShadowInterventionRequest;
use App\Presentation\Http\Response\Shadow\ShadowInterventionCheckResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SkipShadowInterventionController extends AbstractController
{
    #[OA\Post(
        operationId: 'skipShadowIntervention',
        summary: 'Skip a proactive Shadow intervention',
        tags: ['Shadow'],
        responses: [
            new OA\Response(response: 200, description: 'Intervention skipped'),
            new OA\Response(response: 400, description: 'Invalid request'),
        ],
    )]
    #[Route(
        '/api/videos/{videoId}/shadow/sessions/{sessionId}/intervention/{interventionId}/skip',
        name: 'api_videos_shadow_sessions_intervention_skip',
        methods: ['POST'],
    )]
    public function __invoke(
        string $videoId,
        string $sessionId,
        string $interventionId,
        Request $request,
        SkipShadowInterventionHandler $handler,
    ): JsonResponse {
        try {
            new VideoId($videoId);
        } catch (InvalidVideoIdException) {
            return $this->invalidRequestResponse();
        }

        $payload = json_decode($request->getContent(), true);
        $payload = is_array($payload) ? $payload : [];

        try {
            $skipRequest = SkipShadowInterventionRequest::fromArray($payload);
        } catch (InvalidShadowRequestException) {
            return $this->invalidRequestResponse();
        }

        try {
            $result = $handler(new SkipShadowInterventionCommand(
                videoId: $videoId,
                sessionId: $sessionId,
                interventionId: $interventionId,
                currentTimeSeconds: $skipRequest->time,
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
