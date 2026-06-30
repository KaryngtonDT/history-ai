<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Pipeline;

use App\Application\Pipeline\Handlers\LoadPipelineConfigurationHandler;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetPipelineConfigurationController extends AbstractController
{
    #[OA\Get(
        operationId: 'getPipelineConfiguration',
        summary: 'Get pipeline configuration',
        description: 'Returns the latest saved pipeline configuration or platform defaults.',
        tags: ['Pipeline'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Pipeline configuration',
                content: new OA\JsonContent(ref: '#/components/schemas/PipelineConfiguration'),
            ),
        ],
    )]
    #[Route('/api/pipeline', name: 'api_pipeline_get', methods: ['GET'])]
    public function __invoke(LoadPipelineConfigurationHandler $handler): JsonResponse
    {
        $result = $handler();

        return $this->json([
            'id' => $result->id,
            'version' => $result->version,
            'createdAt' => $result->createdAt,
            'updatedAt' => $result->updatedAt,
            'stages' => $result->stages,
        ]);
    }
}
