<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Learning;

use App\Application\Learning\Handlers\GetLearningRecommendationsHandler;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetLearningRecommendationsController extends AbstractController
{
    #[OA\Get(
        operationId: 'getLearningRecommendations',
        summary: 'Get adaptive learning recommendations and hints',
        tags: ['Learning'],
        responses: [
            new OA\Response(response: 200, description: 'Learning recommendations payload'),
        ],
    )]
    #[Route('/api/learning/recommendations', name: 'api_learning_recommendations_get', methods: ['GET'])]
    public function __invoke(Request $request, GetLearningRecommendationsHandler $handler): JsonResponse
    {
        $scopeKey = is_string($request->query->get('scopeKey')) ? $request->query->get('scopeKey') : 'default';

        return $this->json($handler($scopeKey));
    }
}
