<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowExecutive;

use App\Application\ShadowExecutive\Handlers\GetExecutiveAgendaHandler;
use App\Application\ShadowExecutive\Handlers\GetExecutiveDashboardHandler;
use App\Application\ShadowExecutive\Handlers\GetExecutiveHistoryHandler;
use App\Application\ShadowExecutive\Handlers\GetExecutiveRecommendationsHandler;
use App\Application\ShadowExecutive\Handlers\PostApproveDecisionHandler;
use App\Application\ShadowExecutive\Handlers\PostDeferDecisionHandler;
use App\Application\ShadowExecutive\Handlers\PostExecutiveResetHandler;
use App\Application\ShadowExecutive\Handlers\PostRejectDecisionHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ShadowExecutiveController extends AbstractController
{
    #[Route('/api/shadow/executive', name: 'api_shadow_executive_dashboard', methods: ['GET'])]
    public function dashboard(Request $request, GetExecutiveDashboardHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/executive/agenda', name: 'api_shadow_executive_agenda', methods: ['GET'])]
    public function agenda(Request $request, GetExecutiveAgendaHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/executive/recommendations', name: 'api_shadow_executive_recommendations', methods: ['GET'])]
    public function recommendations(Request $request, GetExecutiveRecommendationsHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/executive/history', name: 'api_shadow_executive_history', methods: ['GET'])]
    public function history(Request $request, GetExecutiveHistoryHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/executive/decision/{id}/approve', name: 'api_shadow_executive_decision_approve', methods: ['POST'])]
    public function approveDecision(string $id, Request $request, PostApproveDecisionHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);
        $result = $handler($scopeKey, $id);

        if (isset($result['error'])) {
            return $this->json($result, 404);
        }

        return $this->json($result);
    }

    #[Route('/api/shadow/executive/decision/{id}/reject', name: 'api_shadow_executive_decision_reject', methods: ['POST'])]
    public function rejectDecision(string $id, Request $request, PostRejectDecisionHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);
        $result = $handler($scopeKey, $id);

        if (isset($result['error'])) {
            return $this->json($result, 404);
        }

        return $this->json($result);
    }

    #[Route('/api/shadow/executive/decision/{id}/defer', name: 'api_shadow_executive_decision_defer', methods: ['POST'])]
    public function deferDecision(string $id, Request $request, PostDeferDecisionHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);
        $result = $handler($scopeKey, $id);

        if (isset($result['error'])) {
            return $this->json($result, 404);
        }

        return $this->json($result);
    }

    #[Route('/api/shadow/executive/reset', name: 'api_shadow_executive_reset', methods: ['POST'])]
    public function reset(Request $request, PostExecutiveResetHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request);

        return $this->json($handler($scopeKey));
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
