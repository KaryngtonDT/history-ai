<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Shadow;

use App\Application\Shadow\SessionLearning\Handlers\UpdateSessionLearningPreferencesHandler;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PutSessionLearningPreferencesController extends AbstractController
{
    #[OA\Put(
        operationId: 'updateShadowSessionLearningPreferences',
        summary: 'Update session learning preferences (opt-in/out)',
        tags: ['Shadow'],
        responses: [
            new OA\Response(response: 200, description: 'Updated session learning state'),
            new OA\Response(response: 400, description: 'Invalid request'),
        ],
    )]
    #[Route(
        '/api/videos/{videoId}/shadow/sessions/{sessionId}/learning/preferences',
        name: 'api_videos_shadow_sessions_learning_preferences_put',
        methods: ['PUT'],
    )]
    public function __invoke(
        string $videoId,
        string $sessionId,
        Request $request,
        UpdateSessionLearningPreferencesHandler $handler,
    ): JsonResponse {
        try {
            new VideoId($videoId);
        } catch (InvalidVideoIdException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $view = $handler(
                $videoId,
                $sessionId,
                (bool) ($payload['adaptiveEnabled'] ?? true),
            );
        } catch (InvalidShadowSessionException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($view->toArray());
    }
}
