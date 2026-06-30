<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\AI;

use App\Application\AI\Handlers\ListAIProvidersHandler;
use App\Presentation\Http\Response\AI\AIProvidersResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListAIProvidersController extends AbstractController
{
    #[OA\Get(
        operationId: 'listAIProviders',
        summary: 'List AI engine providers',
        description: 'Returns the platform AI engine registry with providers grouped by capability. Disabled future providers (TTS, voice clone, lip-sync) are included for discovery.',
        tags: ['AI'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'AI providers found',
                content: new OA\JsonContent(ref: '#/components/schemas/AIProvidersList'),
            ),
        ],
    )]
    #[Route('/api/ai/providers', name: 'api_ai_providers_list', methods: ['GET'])]
    public function __invoke(ListAIProvidersHandler $handler): JsonResponse
    {
        return $this->json(AIProvidersResponse::fromSummaries($handler())->toArray());
    }
}
