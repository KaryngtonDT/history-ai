<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Runtime;

use App\Application\EngineAnalytics\EngineAnalyticsContextBuilder;
use App\Application\EngineAnalytics\EngineStatisticsAggregator;
use App\Application\EngineAnalytics\PipelineJobAnalyticsEnricher;
use App\Domain\EngineAnalytics\EngineExecutionHistoryId;
use App\Domain\Pipeline\PipelineStageType;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/runtime')]
final class RuntimeAnalyticsController extends AbstractController
{
    public function __construct(
        private readonly PipelineJobAnalyticsEnricher $analyticsEnricher,
        private readonly EngineStatisticsAggregator $statisticsAggregator,
        private readonly EngineAnalyticsContextBuilder $analyticsContextBuilder,
    ) {
    }

    #[OA\Get(operationId: 'listRuntimeExecutions', tags: ['Runtime'])]
    #[Route('/executions', name: 'api_runtime_executions', methods: ['GET'])]
    public function executions(Request $request): JsonResponse
    {
        $stage = $this->optionalStage($request->query->get('stage'));
        $engineId = $request->query->get('engineId');
        $hardwareProfile = $request->query->get('hardwareProfile');
        $limit = max(1, min(100, (int) $request->query->get('limit', 20)));

        return $this->json([
            'executions' => $this->analyticsEnricher->listExecutions(
                $stage,
                is_string($engineId) && '' !== $engineId ? $engineId : null,
                is_string($hardwareProfile) && '' !== $hardwareProfile ? $hardwareProfile : null,
                $limit,
            ),
        ]);
    }

    #[OA\Get(operationId: 'getRuntimeExecution', tags: ['Runtime'])]
    #[Route('/executions/{executionId}', name: 'api_runtime_execution', methods: ['GET'])]
    public function execution(string $executionId): JsonResponse
    {
        if (!EngineExecutionHistoryId::isValid($executionId)) {
            return $this->json(['error' => 'Invalid execution id.'], 400);
        }

        $execution = $this->analyticsEnricher->getExecution(new EngineExecutionHistoryId($executionId));

        if (null === $execution) {
            return $this->json(['error' => 'Execution not found.'], 404);
        }

        return $this->json($execution);
    }

    #[OA\Get(operationId: 'listRuntimeEngineAnalytics', tags: ['Runtime'])]
    #[Route('/analytics/engines', name: 'api_runtime_analytics_engines', methods: ['GET'])]
    public function engines(Request $request): JsonResponse
    {
        return $this->json([
            'engines' => $this->statisticsAggregator->aggregateEngines(
                $this->optionalStage($request->query->get('stage')),
            ),
        ]);
    }

    #[OA\Get(operationId: 'getRuntimeEngineAnalytics', tags: ['Runtime'])]
    #[Route('/analytics/engines/{engineId}', name: 'api_runtime_analytics_engine', methods: ['GET'])]
    public function engine(string $engineId, Request $request): JsonResponse
    {
        $stage = $this->optionalStage($request->query->get('stage'));
        $engines = $this->statisticsAggregator->aggregateEngines($stage);
        $match = null;

        foreach ($engines as $engine) {
            if (($engine['engineId'] ?? null) === $engineId) {
                $match = $engine;
                break;
            }
        }

        if (null === $match) {
            return $this->json(['error' => 'Engine analytics not found.'], 404);
        }

        return $this->json([
            'engine' => $match,
            'history' => $this->analyticsEnricher->listExecutions($stage, $engineId, null, 50),
            'context' => $this->analyticsContextBuilder->buildForStage($stage),
        ]);
    }

    private function optionalStage(mixed $value): ?PipelineStageType
    {
        if (!is_string($value) || '' === $value) {
            return null;
        }

        return PipelineStageType::tryFrom($value);
    }
}
