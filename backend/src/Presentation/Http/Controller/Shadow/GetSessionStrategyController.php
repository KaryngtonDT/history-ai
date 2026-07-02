<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Shadow;

use App\Application\Shadow\SessionLearning\Handlers\GetSessionStrategyHandler;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetSessionStrategyController extends AbstractController
{
    #[OA\Get(
        operationId: 'getShadowSessionStrategy',
        summary: 'Get resolved teaching strategy for a Shadow session',
        tags: ['Shadow'],
        responses: [
            new OA\Response(response: 200, description: 'Teaching strategy'),
            new OA\Response(response: 400, description: 'Invalid request'),
        ],
    )]
    #[Route(
        '/api/videos/{videoId}/shadow/sessions/{sessionId}/strategy',
        name: 'api_videos_shadow_sessions_strategy_get',
        methods: ['GET'],
    )]
    public function __invoke(
        string $videoId,
        string $sessionId,
        GetSessionStrategyHandler $handler,
    ): JsonResponse {
        try {
            new VideoId($videoId);
            $view = $handler($videoId, $sessionId);
        } catch (InvalidVideoIdException|InvalidShadowSessionException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($view->toArray());
    }
}
