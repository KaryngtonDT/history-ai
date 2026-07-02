<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowMemory;

use App\Application\ShadowMemory\Handlers\GetLearningJourneyHandler;
use App\Application\ShadowMemory\Handlers\GetMemoryConceptsHandler;
use App\Application\ShadowMemory\Handlers\GetMemoryConnectionsHandler;
use App\Application\ShadowMemory\Handlers\GetMemoryMilestonesHandler;
use App\Application\ShadowMemory\Handlers\GetMemoryTimelineHandler;
use App\Application\ShadowMemory\Handlers\GetMemoryVocabularyHandler;
use App\Application\ShadowMemory\Handlers\PostMemorySearchHandler;
use App\Application\ShadowMemory\Handlers\ResetMemoryTimelineHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ShadowMemoryController extends AbstractController
{
    #[Route('/api/shadow/memory/timeline', name: 'api_shadow_memory_timeline', methods: ['GET'])]
    public function timeline(Request $request, GetMemoryTimelineHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/memory/concepts', name: 'api_shadow_memory_concepts', methods: ['GET'])]
    public function concepts(Request $request, GetMemoryConceptsHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/memory/vocabulary', name: 'api_shadow_memory_vocabulary', methods: ['GET'])]
    public function vocabulary(Request $request, GetMemoryVocabularyHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/memory/milestones', name: 'api_shadow_memory_milestones', methods: ['GET'])]
    public function milestones(Request $request, GetMemoryMilestonesHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/memory/connections', name: 'api_shadow_memory_connections', methods: ['GET'])]
    public function connections(Request $request, GetMemoryConnectionsHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/memory/journey', name: 'api_shadow_memory_journey', methods: ['GET'])]
    public function journey(Request $request, GetLearningJourneyHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/memory/search', name: 'api_shadow_memory_search', methods: ['POST'])]
    public function search(Request $request, PostMemorySearchHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], 400);
        }

        return $this->json($handler($this->scopeKey($request, $payload), $payload));
    }

    #[Route('/api/shadow/memory/reset', name: 'api_shadow_memory_reset', methods: ['POST'])]
    public function reset(Request $request, ResetMemoryTimelineHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        return $this->json($handler(
            is_array($payload) && is_string($payload['scopeKey'] ?? null)
                ? $payload['scopeKey']
                : $this->scopeKey($request),
        ));
    }

    /** @param array<string, mixed>|null $payload */
    private function scopeKey(Request $request, ?array $payload = null): string
    {
        if (is_array($payload) && is_string($payload['scopeKey'] ?? null)) {
            return $payload['scopeKey'];
        }

        return is_string($request->query->get('scopeKey')) ? $request->query->get('scopeKey') : 'default';
    }
}
