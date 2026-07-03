<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowBrowser;

use App\Application\ShadowBrowser\Handlers\GetBrowserExplainHandler;
use App\Application\ShadowBrowser\Handlers\GetBrowserHistoryHandler;
use App\Application\ShadowBrowser\Handlers\GetBrowserPermissionsHandler;
use App\Application\ShadowBrowser\Handlers\GetBrowserSessionHandler;
use App\Application\ShadowBrowser\Handlers\PostBrowserConnectHandler;
use App\Application\ShadowBrowser\Handlers\PostBrowserContextHandler;
use App\Application\ShadowBrowser\Handlers\PostBrowserDisconnectHandler;
use App\Application\ShadowBrowser\Handlers\PostBrowserPlatformHandler;
use App\Application\ShadowBrowser\Handlers\PutBrowserPermissionsHandler;
use App\Domain\ShadowBrowser\Exception\InvalidShadowBrowserException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ShadowBrowserController extends AbstractController
{
    #[Route('/api/shadow/browser/session', name: 'api_shadow_browser_session', methods: ['GET'])]
    public function session(Request $request, GetBrowserSessionHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/browser/connect', name: 'api_shadow_browser_connect', methods: ['POST'])]
    public function connect(Request $request, PostBrowserConnectHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);

        try {
            return $this->json($handler($scopeKey, is_array($payload) ? $payload : []));
        } catch (InvalidShadowBrowserException $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }
    }

    #[Route('/api/shadow/browser/disconnect', name: 'api_shadow_browser_disconnect', methods: ['POST'])]
    public function disconnect(Request $request, PostBrowserDisconnectHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);

        try {
            return $this->json($handler($scopeKey));
        } catch (InvalidShadowBrowserException $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }
    }

    #[Route('/api/shadow/browser/context', name: 'api_shadow_browser_context', methods: ['POST'])]
    public function context(Request $request, PostBrowserContextHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);

        try {
            return $this->json($handler($scopeKey, is_array($payload) ? $payload : []));
        } catch (InvalidShadowBrowserException $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }
    }

    #[Route('/api/shadow/browser/platform', name: 'api_shadow_browser_platform', methods: ['POST'])]
    public function platform(Request $request, PostBrowserPlatformHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);

        try {
            return $this->json($handler($scopeKey, is_array($payload) ? $payload : []));
        } catch (InvalidShadowBrowserException $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }
    }

    #[Route('/api/shadow/browser/permissions', name: 'api_shadow_browser_permissions_get', methods: ['GET'])]
    public function getPermissions(Request $request, GetBrowserPermissionsHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/browser/permissions', name: 'api_shadow_browser_permissions_put', methods: ['PUT'])]
    public function putPermissions(Request $request, PutBrowserPermissionsHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);

        return $this->json($handler($scopeKey, is_array($payload) ? $payload : []));
    }

    #[Route('/api/shadow/browser/history', name: 'api_shadow_browser_history', methods: ['GET'])]
    public function history(Request $request, GetBrowserHistoryHandler $handler): JsonResponse
    {
        $limit = is_numeric($request->query->get('limit')) ? (int) $request->query->get('limit') : null;

        return $this->json($handler($this->scopeKey($request), $limit));
    }

    #[Route('/api/shadow/browser/explain', name: 'api_shadow_browser_explain', methods: ['GET'])]
    public function explain(Request $request, GetBrowserExplainHandler $handler): JsonResponse
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
