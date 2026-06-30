<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Optimization\Handlers\GetExecutionOptimizationHandler;
use App\Application\Optimization\Queries\GetExecutionOptimizationQuery;
use App\Domain\Video\Exception\InvalidVideoJobException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoOptimizationController extends AbstractController
{
    #[OA\Get(
        operationId: 'getVideoOptimization',
        summary: 'Get execution optimization for a video',
        description: 'Returns AI Director execution parameter optimization for a video.',
        tags: ['Optimization'],
        parameters: [
            new OA\Parameter(
                name: 'videoId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Execution optimization',
                content: new OA\JsonContent(ref: '#/components/schemas/ExecutionOptimization'),
            ),
            new OA\Response(
                response: 404,
                description: 'Video not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/optimization', name: 'api_videos_optimization_get', methods: ['GET'])]
    public function __invoke(string $videoId, GetExecutionOptimizationHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetExecutionOptimizationQuery($videoId));
        } catch (InvalidVideoJobException) {
            return $this->json(['error' => 'Video not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $result->id,
            'videoId' => $result->videoId,
            'profile' => $result->profile,
            'summary' => $result->summary,
            'estimatedImpact' => $result->estimatedImpact,
            'stages' => $result->stages,
            'explanations' => $result->explanations,
        ]);
    }
}
