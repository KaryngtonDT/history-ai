<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowMentor;

use App\Application\ShadowMentor\Handlers\GetMentorDashboardHandler;
use App\Application\ShadowMentor\Handlers\GetMissionsHandler;
use App\Application\ShadowMentor\Handlers\GetRoadmapHandler;
use App\Application\ShadowMentor\Handlers\PostCompleteMissionHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ShadowMentorController extends AbstractController
{
    #[Route('/api/shadow/mentor', name: 'api_shadow_mentor_dashboard', methods: ['GET'])]
    public function dashboard(Request $request, GetMentorDashboardHandler $handler): JsonResponse
    {
        $conceptKey = is_string($request->query->get('conceptKey')) ? $request->query->get('conceptKey') : null;

        return $this->json($handler($this->scopeKey($request), $conceptKey));
    }

    #[Route('/api/shadow/missions', name: 'api_shadow_missions_list', methods: ['GET'])]
    public function missions(Request $request, GetMissionsHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/roadmap', name: 'api_shadow_roadmap', methods: ['GET'])]
    public function roadmap(Request $request, GetRoadmapHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/missions/{id}/complete', name: 'api_shadow_missions_complete', methods: ['POST'])]
    public function completeMission(string $id, Request $request, PostCompleteMissionHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);
        $result = $handler($scopeKey, $id);

        if (isset($result['error'])) {
            return $this->json($result, 404);
        }

        return $this->json($result);
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
