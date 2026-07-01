<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Learning;

use App\Application\Learning\Handlers\ResetLearningProfileHandler;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PostLearningResetController extends AbstractController
{
    #[OA\Post(
        operationId: 'postLearningReset',
        summary: 'Reset adaptive learning profile signals and derived state',
        tags: ['Learning'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reset learning profile',
                content: new OA\JsonContent(ref: '#/components/schemas/LearningProfile'),
            ),
        ],
    )]
    #[Route('/api/learning/reset', name: 'api_learning_reset_post', methods: ['POST'])]
    public function __invoke(Request $request, ResetLearningProfileHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) && is_string($payload['scopeKey'] ?? null)
            ? $payload['scopeKey']
            : 'default';

        return $this->json($handler($scopeKey));
    }
}
