<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Learning;

use App\Application\Learning\Handlers\GetLearningProfileHandler;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetLearningProfileController extends AbstractController
{
    #[OA\Get(
        operationId: 'getLearningProfile',
        summary: 'Get the adaptive learning profile',
        tags: ['Learning'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Learning profile',
                content: new OA\JsonContent(ref: '#/components/schemas/LearningProfile'),
            ),
        ],
    )]
    #[Route('/api/learning/profile', name: 'api_learning_profile_get', methods: ['GET'])]
    public function __invoke(Request $request, GetLearningProfileHandler $handler): JsonResponse
    {
        $scopeKey = is_string($request->query->get('scopeKey')) ? $request->query->get('scopeKey') : 'default';

        return $this->json($handler($scopeKey));
    }
}
