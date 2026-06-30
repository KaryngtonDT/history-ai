<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\AI;

use App\Application\AI\Handlers\ListAIProvidersHandler;
use App\Presentation\Http\Response\AI\AIProvidersResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListAIProvidersController extends AbstractController
{
    #[Route('/api/ai/providers', name: 'api_ai_providers_list', methods: ['GET'])]
    public function __invoke(ListAIProvidersHandler $handler): JsonResponse
    {
        return $this->json(AIProvidersResponse::fromSummaries($handler())->toArray());
    }
}
