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

final class GetPipelineRecommendationController extends AbstractController
{
    #[OA\Get(
        operationId: 'getPipelineRecommendation',
        summary: 'Get pipeline recommendation',
        description: 'Returns an AI orchestrator pipeline recommendation based on optional video analysis query parameters.',
        tags: ['Orchestrator'],
        parameters: [
            new OA\Parameter(name: 'detectedLanguage', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'durationSeconds', in: 'query', schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'resolution', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'fps', in: 'query', schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'gpuAvailable', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'estimatedVramGb', in: 'query', schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'strategy', in: 'query', schema: new OA\Schema(ref: '#/components/schemas/ProcessingStrategy')),
        ],
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
    #[Route('/api/orchestrator/recommendation', name: 'api_orchestrator_recommendation_get', methods: ['GET'])]
    public function __invoke(
        Request $request,
        RecommendPipelineConfigurationHandler $handler,
        VideoAnalysisRequestMapper $mapper,
    ): JsonResponse {
        try {
            $intelligence = $mapper->intelligenceFromArray([
                'detectedLanguage' => $request->query->get('detectedLanguage'),
                'durationSeconds' => $request->query->get('durationSeconds'),
                'resolution' => $request->query->get('resolution'),
                'fps' => $request->query->get('fps'),
                'segmentCount' => $request->query->get('segmentCount'),
                'transcriptText' => $request->query->get('transcriptText'),
                'gpuAvailable' => $request->query->get('gpuAvailable'),
                'estimatedVramGb' => $request->query->get('estimatedVramGb'),
                'hasSlidesHint' => $request->query->get('hasSlidesHint'),
            ]);
            $strategy = $mapper->parseStrategy($request->query->get('strategy'));
        } catch (InvalidPipelineRecommendationException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $result = $handler(new RecommendPipelineConfigurationQuery($intelligence, $strategy));

        return $this->json([
            'id' => $result->id,
            'strategy' => $result->strategy,
            'explanation' => $result->explanation,
            'estimatedDurationSeconds' => $result->estimatedDurationSeconds,
            'estimatedQuality' => $result->estimatedQuality,
            'estimatedVramGb' => $result->estimatedVramGb,
            'stages' => $result->stages,
            'reasons' => $result->reasons,
        ]);
    }
}
