<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Runtime;

use App\Application\Runtime\RuntimePlatformInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/runtime')]
final class RuntimeController extends AbstractController
{
    public function __construct(private readonly RuntimePlatformInterface $platform)
    {
    }

    #[OA\Get(operationId: 'getRuntimeOverview', tags: ['Runtime'])]
    #[Route('', name: 'api_runtime_overview', methods: ['GET'])]
    public function overview(): JsonResponse
    {
        return $this->json($this->platform->overview());
    }

    #[OA\Get(operationId: 'getRuntimeReadiness', tags: ['Runtime'])]
    #[Route('/readiness', name: 'api_runtime_readiness', methods: ['GET'])]
    public function readiness(): JsonResponse
    {
        return $this->json($this->platform->readiness());
    }

    #[OA\Get(operationId: 'getRuntimeHealth', tags: ['Runtime'])]
    #[Route('/health', name: 'api_runtime_health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return $this->json($this->platform->health());
    }

    #[OA\Get(operationId: 'listRuntimeEngines', tags: ['Runtime'])]
    #[Route('/engines', name: 'api_runtime_engines', methods: ['GET'])]
    public function engines(): JsonResponse
    {
        return $this->json(['engines' => $this->platform->engines()]);
    }

    #[OA\Get(operationId: 'getRuntimeCatalog', tags: ['Runtime'])]
    #[Route('/catalog', name: 'api_runtime_catalog', methods: ['GET'])]
    public function catalog(): JsonResponse
    {
        return $this->json($this->platform->catalog());
    }

    #[OA\Get(operationId: 'getRuntimeRecommendations', tags: ['Runtime'])]
    #[Route('/recommendations', name: 'api_runtime_recommendations', methods: ['GET'])]
    public function recommendations(): JsonResponse
    {
        return $this->json(['recommendations' => $this->platform->recommendations()]);
    }

    #[OA\Get(operationId: 'getRuntimeProfiles', tags: ['Runtime'])]
    #[Route('/profiles', name: 'api_runtime_profiles', methods: ['GET'])]
    public function profiles(): JsonResponse
    {
        return $this->json(['profiles' => $this->platform->profiles()]);
    }

    #[OA\Post(operationId: 'testRuntimeEngine', tags: ['Runtime'])]
    #[Route('/engines/{id}/test', name: 'api_runtime_engine_test', methods: ['POST'])]
    public function testEngine(string $id): JsonResponse
    {
        return $this->json($this->platform->testEngine($id));
    }

    #[OA\Post(operationId: 'runRuntimeBenchmark', tags: ['Runtime'])]
    #[Route('/benchmark', name: 'api_runtime_benchmark', methods: ['POST'])]
    public function benchmark(Request $request): JsonResponse
    {
        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($request->getContent(), true);
        $engineId = is_array($payload) && is_string($payload['engineId'] ?? null) ? $payload['engineId'] : null;

        return $this->json($this->platform->benchmark($engineId));
    }

    #[OA\Post(operationId: 'runRuntimeFullBenchmark', tags: ['Runtime'])]
    #[Route('/benchmark/full', name: 'api_runtime_benchmark_full', methods: ['POST'])]
    public function benchmarkFull(): JsonResponse
    {
        return $this->json($this->platform->benchmark());
    }

    #[OA\Post(operationId: 'validateRuntimePipeline', tags: ['Runtime'])]
    #[Route('/pipeline/validate', name: 'api_runtime_pipeline_validate', methods: ['POST'])]
    public function validatePipeline(): JsonResponse
    {
        return $this->json($this->platform->validatePipeline());
    }

    #[OA\Put(operationId: 'updateRuntimeProfile', tags: ['Runtime'])]
    #[Route('/profile', name: 'api_runtime_profile_update', methods: ['PUT'])]
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $payload */
        $payload = json_decode($request->getContent(), true) ?: [];

        return $this->json($this->platform->updateProfile($payload));
    }

    #[OA\Put(operationId: 'updateRuntimeSelection', tags: ['Runtime'])]
    #[Route('/selection', name: 'api_runtime_selection_update', methods: ['PUT'])]
    public function updateSelection(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $payload */
        $payload = json_decode($request->getContent(), true) ?: [];

        return $this->json($this->platform->updateSelection($payload));
    }

    #[OA\Post(operationId: 'provisionRuntimeEngine', tags: ['Runtime'])]
    #[Route('/engines/{id}/provision', name: 'api_runtime_engine_provision', methods: ['POST'])]
    public function provisionEngine(string $id): JsonResponse
    {
        return $this->json($this->platform->provisionEngine($id));
    }

    #[OA\Post(operationId: 'provisionAllRuntimeEngines', tags: ['Runtime'])]
    #[Route('/provision', name: 'api_runtime_provision_all', methods: ['POST'])]
    public function provisionAll(): JsonResponse
    {
        return $this->json($this->platform->provisionAll());
    }

    #[OA\Post(operationId: 'provisionCompatibleRuntimeEngines', tags: ['Runtime'])]
    #[Route('/provision/compatible', name: 'api_runtime_provision_compatible', methods: ['POST'])]
    public function provisionCompatible(): JsonResponse
    {
        return $this->json($this->platform->provisionCompatibleAll());
    }

    #[OA\Get(operationId: 'getRuntimeProvisioningPlan', tags: ['Runtime'])]
    #[Route('/provision/plan', name: 'api_runtime_provision_plan', methods: ['GET'])]
    public function provisioningPlan(): JsonResponse
    {
        return $this->json($this->platform->provisioningPlan());
    }

    #[OA\Get(operationId: 'getRuntimeReport', tags: ['Runtime'])]
    #[Route('/report/{pipelineId}', name: 'api_runtime_report', methods: ['GET'])]
    public function report(string $pipelineId): JsonResponse
    {
        $report = $this->platform->report($pipelineId);

        if (null === $report) {
            return $this->json(['error' => 'Report not found'], 404);
        }

        return $this->json($report);
    }

    #[OA\Get(operationId: 'getRuntimeHardware', tags: ['Runtime'])]
    #[Route('/hardware', name: 'api_runtime_hardware', methods: ['GET'])]
    public function hardware(): JsonResponse
    {
        return $this->json($this->platform->hardware());
    }

    #[OA\Get(operationId: 'getRuntimeHardwareProfile', tags: ['Runtime'])]
    #[Route('/hardware/profile', name: 'api_runtime_hardware_profile', methods: ['GET'])]
    public function hardwareProfile(): JsonResponse
    {
        return $this->json($this->platform->hardwareProfile());
    }

    #[OA\Get(operationId: 'getRuntimeCompatibility', tags: ['Runtime'])]
    #[Route('/compatibility', name: 'api_runtime_compatibility', methods: ['GET'])]
    public function compatibility(): JsonResponse
    {
        return $this->json($this->platform->compatibility());
    }

    #[OA\Get(operationId: 'getRuntimeEngineCompatibility', tags: ['Runtime'])]
    #[Route('/engines/{id}/compatibility', name: 'api_runtime_engine_compatibility', methods: ['GET'])]
    public function engineCompatibility(string $id): JsonResponse
    {
        $result = $this->platform->engineCompatibility($id);

        if (null === $result) {
            return $this->json(['error' => 'Engine not found'], 404);
        }

        return $this->json($result);
    }

    #[OA\Get(operationId: 'getRuntimeEngineBlockedReason', tags: ['Runtime'])]
    #[Route('/engines/{id}/blocked-reason', name: 'api_runtime_engine_blocked_reason', methods: ['GET'])]
    public function engineBlockedReason(string $id): JsonResponse
    {
        $result = $this->platform->engineBlockedReason($id);

        if (null === $result) {
            return $this->json(['error' => 'Engine not found'], 404);
        }

        return $this->json($result);
    }

    #[OA\Get(operationId: 'getRuntimeCapabilityMaturity', tags: ['Runtime'])]
    #[Route('/capabilities/maturity', name: 'api_runtime_capability_maturity', methods: ['GET'])]
    public function capabilityMaturity(): JsonResponse
    {
        return $this->json($this->platform->capabilityMaturity());
    }

    #[OA\Post(operationId: 'resolveRuntimeEngine', tags: ['Runtime'])]
    #[Route('/resolve', name: 'api_runtime_resolve', methods: ['POST'])]
    public function resolve(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $payload */
        $payload = json_decode($request->getContent(), true) ?: [];

        return $this->json($this->platform->resolve($payload));
    }

    #[OA\Get(operationId: 'getRuntimeSelection', tags: ['Runtime'])]
    #[Route('/selection', name: 'api_runtime_selection_get', methods: ['GET'])]
    public function selection(): JsonResponse
    {
        return $this->json($this->platform->selection());
    }

    #[OA\Get(operationId: 'listRuntimeCapabilities', tags: ['Runtime'])]
    #[Route('/capabilities', name: 'api_runtime_capabilities', methods: ['GET'])]
    public function capabilities(): JsonResponse
    {
        return $this->json(['capabilities' => $this->platform->capabilities()]);
    }

    #[OA\Get(operationId: 'getRuntimeCapabilitySelectionView', tags: ['Runtime'])]
    #[Route('/capabilities/{capability}/selection-view', name: 'api_runtime_capability_selection_view', methods: ['GET'])]
    public function capabilitySelectionView(string $capability): JsonResponse
    {
        return $this->json($this->platform->capabilitySelectionView($capability));
    }

    #[OA\Get(operationId: 'getRuntimeEngineManagement', tags: ['Runtime'])]
    #[Route('/engines/management', name: 'api_runtime_engine_management', methods: ['GET'])]
    public function engineManagement(): JsonResponse
    {
        return $this->json($this->platform->engineManagement());
    }

    #[OA\Post(operationId: 'installRuntimeEngine', tags: ['Runtime'])]
    #[Route('/engines/{id}/install', name: 'api_runtime_engine_install', methods: ['POST'])]
    public function installEngine(string $id): JsonResponse
    {
        return $this->json($this->platform->installEngine($id));
    }

    #[OA\Post(operationId: 'updateRuntimeEngine', tags: ['Runtime'])]
    #[Route('/engines/{id}/update', name: 'api_runtime_engine_update', methods: ['POST'])]
    public function updateEngine(string $id): JsonResponse
    {
        return $this->json($this->platform->updateEngine($id));
    }

    #[OA\Post(operationId: 'repairRuntimeEngine', tags: ['Runtime'])]
    #[Route('/engines/{id}/repair', name: 'api_runtime_engine_repair', methods: ['POST'])]
    public function repairEngine(string $id): JsonResponse
    {
        return $this->json($this->platform->repairEngine($id));
    }

    #[OA\Delete(operationId: 'removeRuntimeEngine', tags: ['Runtime'])]
    #[Route('/engines/{id}', name: 'api_runtime_engine_remove', methods: ['DELETE'])]
    public function removeEngine(string $id): JsonResponse
    {
        return $this->json($this->platform->removeEngine($id));
    }

    #[OA\Post(operationId: 'validateRuntimeEngine', tags: ['Runtime'])]
    #[Route('/engines/{id}/validate', name: 'api_runtime_engine_validate', methods: ['POST'])]
    public function validateEngine(string $id): JsonResponse
    {
        return $this->json($this->platform->validateEngine($id));
    }

    #[OA\Get(operationId: 'getRuntimeEngineMetadata', tags: ['Runtime'])]
    #[Route('/engines/{id}/metadata', name: 'api_runtime_engine_metadata', methods: ['GET'])]
    public function engineMetadata(string $id): JsonResponse
    {
        $metadata = $this->platform->engineMetadata($id);
        if (null === $metadata) {
            return $this->json(['error' => 'Engine not found'], 404);
        }

        return $this->json($metadata);
    }

    #[OA\Get(operationId: 'getRuntimeRecommendationProfiles', tags: ['Runtime'])]
    #[Route('/recommendations/profiles', name: 'api_runtime_recommendation_profiles', methods: ['GET'])]
    public function recommendationProfiles(): JsonResponse
    {
        return $this->json($this->platform->recommendationProfiles());
    }

    #[OA\Get(operationId: 'getRuntimeDoctorReport', tags: ['Runtime'])]
    #[Route('/doctor', name: 'api_runtime_doctor', methods: ['GET'])]
    public function doctorReport(): JsonResponse
    {
        return $this->json($this->platform->doctorReport());
    }

    #[OA\Get(operationId: 'listRuntimeNotifications', tags: ['Runtime'])]
    #[Route('/notifications', name: 'api_runtime_notifications', methods: ['GET'])]
    public function notifications(): JsonResponse
    {
        return $this->json(['notifications' => $this->platform->notifications()]);
    }
}
