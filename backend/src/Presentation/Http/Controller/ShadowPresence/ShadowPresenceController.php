<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowPresence;

use App\Application\ShadowPresence\Handlers\GetPresenceContextHandler;
use App\Application\ShadowPresence\Handlers\GetPresenceExplainHandler;
use App\Application\ShadowPresence\Handlers\GetPresenceHistoryHandler;
use App\Application\ShadowPresence\Handlers\GetPresenceSessionHandler;
use App\Application\ShadowPresence\Handlers\PostPresenceConnectHandler;
use App\Application\ShadowPresence\Handlers\PostPresenceDisconnectHandler;
use App\Application\ShadowPresence\Handlers\PutPresencePreferencesHandler;
use App\Domain\ShadowPresence\Exception\InvalidShadowPresenceException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ShadowPresenceController extends AbstractController
{
    #[Route('/api/shadow/presence/session', name: 'api_shadow_presence_session', methods: ['GET'])]
    public function session(Request $request, GetPresenceSessionHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/presence/connect', name: 'api_shadow_presence_connect', methods: ['POST'])]
    public function connect(Request $request, PostPresenceConnectHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);

        try {
            return $this->json($handler($scopeKey, is_array($payload) ? $payload : []));
        } catch (InvalidShadowPresenceException $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }
    }

    #[Route('/api/shadow/presence/disconnect', name: 'api_shadow_presence_disconnect', methods: ['POST'])]
    public function disconnect(Request $request, PostPresenceDisconnectHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);

        try {
            return $this->json($handler($scopeKey));
        } catch (InvalidShadowPresenceException $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }
    }

    #[Route('/api/shadow/presence/context', name: 'api_shadow_presence_context', methods: ['GET'])]
    public function context(Request $request, GetPresenceContextHandler $handler): JsonResponse
    {
        $surface = is_string($request->query->get('surface')) ? $request->query->get('surface') : null;

        return $this->json($handler($this->scopeKey($request), $surface));
    }

    #[Route('/api/shadow/presence/preferences', name: 'api_shadow_presence_preferences', methods: ['PUT'])]
    public function preferences(Request $request, PutPresencePreferencesHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);

        return $this->json($handler($scopeKey, is_array($payload) ? $payload : []));
    }

    #[Route('/api/shadow/presence/history', name: 'api_shadow_presence_history', methods: ['GET'])]
    public function history(Request $request, GetPresenceHistoryHandler $handler): JsonResponse
    {
        $limit = is_numeric($request->query->get('limit')) ? (int) $request->query->get('limit') : null;

        return $this->json($handler($this->scopeKey($request), $limit));
    }

    #[Route('/api/shadow/presence/explain', name: 'api_shadow_presence_explain', methods: ['GET'])]
    public function explain(Request $request, GetPresenceExplainHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
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
