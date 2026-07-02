<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowKnowledge;

use App\Application\ShadowKnowledge\Handlers\GetKnowledgeGapsHandler;
use App\Application\ShadowKnowledge\Handlers\GetKnowledgeGraphHandler;
use App\Application\ShadowKnowledge\Handlers\GetKnowledgeNodeHandler;
use App\Application\ShadowKnowledge\Handlers\GetKnowledgePathHandler;
use App\Application\ShadowKnowledge\Handlers\GetKnowledgeRelatedHandler;
use App\Application\ShadowKnowledge\Handlers\PostKnowledgeRebuildHandler;
use App\Application\ShadowKnowledge\Handlers\PostKnowledgeResetHandler;
use App\Application\ShadowKnowledge\Handlers\PostKnowledgeSearchHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ShadowKnowledgeController extends AbstractController
{
    #[Route('/api/shadow/knowledge/graph', name: 'api_shadow_knowledge_graph', methods: ['GET'])]
    public function graph(Request $request, GetKnowledgeGraphHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/knowledge/node/{id}', name: 'api_shadow_knowledge_node', methods: ['GET'])]
    public function node(string $id, Request $request, GetKnowledgeNodeHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request), $id));
    }

    #[Route('/api/shadow/knowledge/path', name: 'api_shadow_knowledge_path', methods: ['GET'])]
    public function path(Request $request, GetKnowledgePathHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/knowledge/gaps', name: 'api_shadow_knowledge_gaps', methods: ['GET'])]
    public function gaps(Request $request, GetKnowledgeGapsHandler $handler): JsonResponse
    {
        $goalKey = is_string($request->query->get('goalKey')) ? $request->query->get('goalKey') : 'kubernetes';

        return $this->json($handler($this->scopeKey($request), $goalKey));
    }

    #[Route('/api/shadow/knowledge/related', name: 'api_shadow_knowledge_related', methods: ['GET'])]
    public function related(Request $request, GetKnowledgeRelatedHandler $handler): JsonResponse
    {
        $key = is_string($request->query->get('key')) ? $request->query->get('key') : '';

        if ('' === $key) {
            return $this->json(['error' => 'Missing key query parameter.'], 400);
        }

        return $this->json($handler($this->scopeKey($request), $key));
    }

    #[Route('/api/shadow/knowledge/search', name: 'api_shadow_knowledge_search', methods: ['POST'])]
    public function search(Request $request, PostKnowledgeSearchHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], 400);
        }

        return $this->json($handler($this->scopeKey($request, $payload), $payload));
    }

    #[Route('/api/shadow/knowledge/rebuild', name: 'api_shadow_knowledge_rebuild', methods: ['POST'])]
    public function rebuild(Request $request, PostKnowledgeRebuildHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        return $this->json($handler(
            is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request),
        ));
    }

    #[Route('/api/shadow/knowledge/reset', name: 'api_shadow_knowledge_reset', methods: ['POST'])]
    public function reset(Request $request, PostKnowledgeResetHandler $handler): JsonResponse
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
