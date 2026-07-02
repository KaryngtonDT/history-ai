<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Shadow;

use App\Application\Shadow\SessionLearning\Handlers\RecordSessionObservationHandler;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostSessionObservationController extends AbstractController
{
    #[OA\Post(
        operationId: 'recordShadowSessionObservation',
        summary: 'Record a learning observation during Shadow watch mode',
        tags: ['Shadow'],
        responses: [
            new OA\Response(response: 200, description: 'Updated session learning state'),
            new OA\Response(response: 400, description: 'Invalid request'),
        ],
    )]
    #[Route(
        '/api/videos/{videoId}/shadow/sessions/{sessionId}/learning/observations',
        name: 'api_videos_shadow_sessions_learning_observations_post',
        methods: ['POST'],
    )]
    public function __invoke(
        string $videoId,
        string $sessionId,
        Request $request,
        RecordSessionObservationHandler $handler,
    ): JsonResponse {
        try {
            new VideoId($videoId);
        } catch (InvalidVideoIdException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload) || !isset($payload['type'])) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $view = $handler(
                $videoId,
                $sessionId,
                (string) $payload['type'],
                (float) ($payload['timeSeconds'] ?? 0),
                isset($payload['detail']) ? (string) $payload['detail'] : null,
            );
        } catch (InvalidShadowSessionException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($view->toArray());
    }
}
