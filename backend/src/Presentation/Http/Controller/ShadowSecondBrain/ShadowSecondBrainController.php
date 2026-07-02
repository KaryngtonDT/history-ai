<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowSecondBrain;

use App\Application\ShadowSecondBrain\Handlers\DeleteBrainBookmarkHandler;
use App\Application\ShadowSecondBrain\Handlers\GetBrainConceptHandler;
use App\Application\ShadowSecondBrain\Handlers\GetBrainConceptsHandler;
use App\Application\ShadowSecondBrain\Handlers\GetBrainDashboardHandler;
use App\Application\ShadowSecondBrain\Handlers\GetBrainDiffHandler;
use App\Application\ShadowSecondBrain\Handlers\GetBrainSearchHandler;
use App\Application\ShadowSecondBrain\Handlers\GetBrainTimelineHandler;
use App\Application\ShadowSecondBrain\Handlers\PostBrainBookmarkHandler;
use App\Application\ShadowSecondBrain\Handlers\PostBrainNoteHandler;
use App\Application\ShadowSecondBrain\Handlers\PostBrainRebuildHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ShadowSecondBrainController extends AbstractController
{
    #[Route('/api/shadow/brain', name: 'api_shadow_brain_dashboard', methods: ['GET'])]
    public function dashboard(Request $request, GetBrainDashboardHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/brain/concepts', name: 'api_shadow_brain_concepts', methods: ['GET'])]
    public function concepts(Request $request, GetBrainConceptsHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/brain/concept/{id}', name: 'api_shadow_brain_concept', methods: ['GET'])]
    public function concept(string $id, Request $request, GetBrainConceptHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request), $id));
    }

    #[Route('/api/shadow/brain/search', name: 'api_shadow_brain_search', methods: ['GET'])]
    public function search(Request $request, GetBrainSearchHandler $handler): JsonResponse
    {
        $query = is_string($request->query->get('q')) ? $request->query->get('q') : '';

        if ('' === trim($query)) {
            return $this->json(['error' => 'Missing q query parameter.'], 400);
        }

        return $this->json($handler($this->scopeKey($request), $query));
    }

    #[Route('/api/shadow/brain/timeline', name: 'api_shadow_brain_timeline', methods: ['GET'])]
    public function timeline(Request $request, GetBrainTimelineHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/brain/diff', name: 'api_shadow_brain_diff', methods: ['GET'])]
    public function diff(Request $request, GetBrainDiffHandler $handler): JsonResponse
    {
        $resourceType = is_string($request->query->get('resourceType')) ? $request->query->get('resourceType') : '';
        $resourceId = is_string($request->query->get('resourceId')) ? $request->query->get('resourceId') : '';

        if ('' === $resourceType || '' === $resourceId) {
            return $this->json(['error' => 'Missing resourceType or resourceId query parameter.'], 400);
        }

        $conceptKeys = [];

        if (is_string($request->query->get('conceptKeys'))) {
            $conceptKeys = array_values(array_filter(
                array_map('trim', explode(',', $request->query->get('conceptKeys'))),
                static fn (string $key): bool => '' !== $key,
            ));
        }

        return $this->json($handler(
            $this->scopeKey($request),
            $resourceType,
            $resourceId,
            $conceptKeys,
        ));
    }

    #[Route('/api/shadow/brain/bookmark', name: 'api_shadow_brain_bookmark_create', methods: ['POST'])]
    public function bookmark(Request $request, PostBrainBookmarkHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], 400);
        }

        return $this->json($handler(
            is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request),
            $payload,
        ));
    }

    #[Route('/api/shadow/brain/note', name: 'api_shadow_brain_note_create', methods: ['POST'])]
    public function note(Request $request, PostBrainNoteHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], 400);
        }

        return $this->json($handler(
            is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request),
            $payload,
        ));
    }

    #[Route('/api/shadow/brain/bookmark/{id}', name: 'api_shadow_brain_bookmark_delete', methods: ['DELETE'])]
    public function deleteBookmark(string $id, Request $request, DeleteBrainBookmarkHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request), $id));
    }

    #[Route('/api/shadow/brain/rebuild', name: 'api_shadow_brain_rebuild', methods: ['POST'])]
    public function rebuild(Request $request, PostBrainRebuildHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        return $this->json($handler(
            is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request),
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
