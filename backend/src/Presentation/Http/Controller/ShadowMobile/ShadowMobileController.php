<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowMobile;

use App\Application\Mobile\Handlers\GetMobileConnectionsHandler;
use App\Application\Mobile\Handlers\GetMobileHealthHandler;
use App\Application\Mobile\Handlers\GetMobileMissionsHandler;
use App\Application\Mobile\Handlers\GetMobileProfileHandler;
use App\Application\Mobile\Handlers\GetMobileRevisionsHandler;
use App\Application\Mobile\Handlers\GetMobileServerHandler;
use App\Application\Mobile\Handlers\GetMobileTodayHandler;
use App\Application\Mobile\Handlers\PostMobileDeviceHandler;
use App\Application\Mobile\Handlers\PostMobilePushTokenHandler;
use App\Application\Mobile\Handlers\PostMobileSyncHandler;
use App\Application\Mobile\Handlers\PutMobileConnectionHandler;
use App\Application\Mobile\Handlers\PutMobilePreferencesHandler;
use App\Domain\Mobile\Exception\InvalidMobileException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ShadowMobileController extends AbstractController
{
    #[Route('/api/shadow/mobile/profile', name: 'api_shadow_mobile_profile', methods: ['GET'])]
    public function profile(Request $request, GetMobileProfileHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/mobile/today', name: 'api_shadow_mobile_today', methods: ['GET'])]
    public function today(Request $request, GetMobileTodayHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/mobile/missions', name: 'api_shadow_mobile_missions', methods: ['GET'])]
    public function missions(Request $request, GetMobileMissionsHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/mobile/revisions', name: 'api_shadow_mobile_revisions', methods: ['GET'])]
    public function revisions(Request $request, GetMobileRevisionsHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/mobile/server', name: 'api_shadow_mobile_server', methods: ['GET'])]
    public function server(Request $request, GetMobileServerHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/mobile/health', name: 'api_shadow_mobile_health', methods: ['GET'])]
    public function health(Request $request, GetMobileHealthHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/mobile/connections', name: 'api_shadow_mobile_connections', methods: ['GET'])]
    public function connections(Request $request, GetMobileConnectionsHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/mobile/device', name: 'api_shadow_mobile_device', methods: ['POST'])]
    public function device(Request $request, PostMobileDeviceHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);

        try {
            return $this->json($handler($scopeKey, is_array($payload) ? $payload : []));
        } catch (InvalidMobileException $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }
    }

    #[Route('/api/shadow/mobile/sync', name: 'api_shadow_mobile_sync', methods: ['POST'])]
    public function sync(Request $request, PostMobileSyncHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);

        try {
            return $this->json($handler($scopeKey, is_array($payload) ? $payload : []));
        } catch (InvalidMobileException $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }
    }

    #[Route('/api/shadow/mobile/preferences', name: 'api_shadow_mobile_preferences', methods: ['PUT'])]
    public function preferences(Request $request, PutMobilePreferencesHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);

        return $this->json($handler($scopeKey, is_array($payload) ? $payload : []));
    }

    #[Route('/api/shadow/mobile/connection', name: 'api_shadow_mobile_connection', methods: ['PUT'])]
    public function connection(Request $request, PutMobileConnectionHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);

        return $this->json($handler($scopeKey, is_array($payload) ? $payload : []));
    }

    #[Route('/api/shadow/mobile/push-token', name: 'api_shadow_mobile_push_token', methods: ['POST'])]
    public function pushToken(Request $request, PostMobilePushTokenHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);

        try {
            return $this->json($handler($scopeKey, is_array($payload) ? $payload : []));
        } catch (InvalidMobileException $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }
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
