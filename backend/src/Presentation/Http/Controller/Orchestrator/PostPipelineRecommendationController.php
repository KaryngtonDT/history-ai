<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Orchestrator;

use App\Application\Orchestrator\Handlers\RecommendPipelineConfigurationHandler;
use App\Application\Orchestrator\Queries\RecommendPipelineConfigurationQuery;
use App\Application\Orchestrator\VideoAnalysisRequestMapper;
use App\Domain\Orchestrator\Exception\InvalidPipelineRecommendationException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostPipelineRecommendationController extends AbstractController
{
    #[OA\Post(
        operationId: 'postPipelineRecommendation',
        summary: 'Request pipeline recommendation',
        description: 'Returns an AI orchestrator pipeline recommendation from a video analysis payload.',
        tags: ['Orchestrator'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/VideoAnalysis'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Pipeline recommendation',
                content: new OA\JsonContent(ref: '#/components/schemas/PipelineRecommendation'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/orchestrator/recommendation', name: 'api_orchestrator_recommendation_post', methods: ['POST'])]
    public function __invoke(
        Request $request,
        RecommendPipelineConfigurationHandler $handler,
        VideoAnalysisRequestMapper $mapper,
    ): JsonResponse {
        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $analysis = $mapper->fromArray($payload);
            $strategy = $mapper->parseStrategy($payload['strategy'] ?? null);
        } catch (InvalidPipelineRecommendationException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $result = $handler(new RecommendPipelineConfigurationQuery($analysis, $strategy));

        return $this->json([
            'id' => $result->id,
            'strategy' => $result->strategy,
            'explanation' => $result->explanation,
            'estimatedDurationSeconds' => $result->estimatedDurationSeconds,
            'estimatedQuality' => $result->estimatedQuality,
            'estimatedVramGb' => $result->estimatedVramGb,
            'stages' => $result->stages,
        ]);
    }
}
