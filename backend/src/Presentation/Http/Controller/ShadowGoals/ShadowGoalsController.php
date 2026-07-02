<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowGoals;

use App\Application\ShadowMentor\Handlers\DeleteGoalHandler;
use App\Application\ShadowMentor\Handlers\GetGoalsHandler;
use App\Application\ShadowMentor\Handlers\PostCreateGoalHandler;
use App\Application\ShadowMentor\Handlers\PostGoalsResetHandler;
use App\Application\ShadowMentor\Handlers\PutUpdateGoalHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ShadowGoalsController extends AbstractController
{
    #[Route('/api/shadow/goals', name: 'api_shadow_goals_list', methods: ['GET'])]
    public function list(Request $request, GetGoalsHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/goals', name: 'api_shadow_goals_create', methods: ['POST'])]
    public function create(Request $request, PostCreateGoalHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], 400);
        }

        return $this->json($handler($this->scopeKey($request, $payload), $payload), 201);
    }

    #[Route('/api/shadow/goals/reset', name: 'api_shadow_goals_reset', methods: ['POST'])]
    public function reset(Request $request, PostGoalsResetHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        return $this->json($handler(
            is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request),
        ));
    }

    #[Route('/api/shadow/goals/{id}', name: 'api_shadow_goals_update', methods: ['PUT'])]
    public function update(string $id, Request $request, PutUpdateGoalHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], 400);
        }

        $result = $handler($this->scopeKey($request, $payload), $id, $payload);

        if (isset($result['error'])) {
            return $this->json($result, 404);
        }

        return $this->json($result);
    }

    #[Route('/api/shadow/goals/{id}', name: 'api_shadow_goals_delete', methods: ['DELETE'])]
    public function delete(string $id, Request $request, DeleteGoalHandler $handler): Response
    {
        $handler($this->scopeKey($request), $id);

        return new Response('', 204);
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
