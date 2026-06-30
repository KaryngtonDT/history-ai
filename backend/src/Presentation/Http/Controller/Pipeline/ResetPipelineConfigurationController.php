<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Pipeline;

use App\Application\Pipeline\Handlers\ResetPipelineConfigurationHandler;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ResetPipelineConfigurationController extends AbstractController
{
    #[OA\Post(
        operationId: 'resetPipelineConfiguration',
        summary: 'Reset pipeline configuration',
        description: 'Deletes saved configuration and restores platform default providers.',
        tags: ['Pipeline'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reset pipeline configuration',
                content: new OA\JsonContent(ref: '#/components/schemas/PipelineConfiguration'),
            ),
        ],
    )]
    #[Route('/api/pipeline/reset', name: 'api_pipeline_reset', methods: ['POST'])]
    public function __invoke(ResetPipelineConfigurationHandler $handler): JsonResponse
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
