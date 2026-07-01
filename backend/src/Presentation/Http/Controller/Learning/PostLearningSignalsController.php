<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Learning;

use App\Application\Learning\Handlers\RecordLearningSignalsHandler;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostLearningSignalsController extends AbstractController
{
    #[OA\Post(
        operationId: 'postLearningSignals',
        summary: 'Record learning signals for adaptive intelligence',
        tags: ['Learning'],
    )]
    #[Route('/api/learning/signals', name: 'api_learning_signals_post', methods: ['POST'])]
    public function __invoke(Request $request, RecordLearningSignalsHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $scopeKey = is_string($payload['scopeKey'] ?? null) ? $payload['scopeKey'] : 'default';

        return $this->json($handler($scopeKey, $payload));
    }
}
