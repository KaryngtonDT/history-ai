<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Learning;

use App\Application\Learning\Handlers\UpdateLearningPreferencesHandler;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PutLearningPreferencesController extends AbstractController
{
    #[OA\Put(
        operationId: 'putLearningPreferences',
        summary: 'Update adaptive learning preferences',
        tags: ['Learning'],
    )]
    #[Route('/api/learning/preferences', name: 'api_learning_preferences_put', methods: ['PUT'])]
    public function __invoke(Request $request, UpdateLearningPreferencesHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $scopeKey = is_string($payload['scopeKey'] ?? null) ? $payload['scopeKey'] : 'default';

        return $this->json($handler($scopeKey, $payload));
    }
}
