<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowTeaching;

use App\Application\ShadowTeaching\Handlers\GetTeachingCurrentHandler;
use App\Application\ShadowTeaching\Handlers\GetTeachingExercisesHandler;
use App\Application\ShadowTeaching\Handlers\GetTeachingObjectivesHandler;
use App\Application\ShadowTeaching\Handlers\GetTeachingPathHandler;
use App\Application\ShadowTeaching\Handlers\GetTeachingRevisionsHandler;
use App\Application\ShadowTeaching\Handlers\PostTeachingCheckpointCompleteHandler;
use App\Application\ShadowTeaching\Handlers\PostTeachingExerciseAnswerHandler;
use App\Application\ShadowTeaching\Handlers\PostTeachingResetHandler;
use App\Application\ShadowTeaching\Handlers\PutTeachingPreferencesHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ShadowTeachingController extends AbstractController
{
    #[Route('/api/shadow/teaching/path', name: 'api_shadow_teaching_path', methods: ['GET'])]
    public function path(Request $request, GetTeachingPathHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/teaching/current', name: 'api_shadow_teaching_current', methods: ['GET'])]
    public function current(Request $request, GetTeachingCurrentHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/teaching/objectives', name: 'api_shadow_teaching_objectives', methods: ['GET'])]
    public function objectives(Request $request, GetTeachingObjectivesHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/teaching/revisions', name: 'api_shadow_teaching_revisions', methods: ['GET'])]
    public function revisions(Request $request, GetTeachingRevisionsHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/teaching/exercises', name: 'api_shadow_teaching_exercises', methods: ['GET'])]
    public function exercises(Request $request, GetTeachingExercisesHandler $handler): JsonResponse
    {
        return $this->json($handler($this->scopeKey($request)));
    }

    #[Route('/api/shadow/teaching/exercise/{id}/answer', name: 'api_shadow_teaching_exercise_answer', methods: ['POST'])]
    public function answerExercise(
        string $id,
        Request $request,
        PostTeachingExerciseAnswerHandler $handler,
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], 400);
        }

        return $this->json($handler($this->scopeKey($request, $payload), $id, $payload));
    }

    #[Route('/api/shadow/teaching/checkpoint/{id}/complete', name: 'api_shadow_teaching_checkpoint_complete', methods: ['POST'])]
    public function completeCheckpoint(
        string $id,
        Request $request,
        PostTeachingCheckpointCompleteHandler $handler,
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);

        return $this->json($handler(
            is_array($payload) ? $this->scopeKey($request, $payload) : $this->scopeKey($request),
            $id,
        ));
    }

    #[Route('/api/shadow/teaching/preferences', name: 'api_shadow_teaching_preferences', methods: ['PUT'])]
    public function preferences(Request $request, PutTeachingPreferencesHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], 400);
        }

        return $this->json($handler($this->scopeKey($request, $payload), $payload));
    }

    #[Route('/api/shadow/teaching/reset', name: 'api_shadow_teaching_reset', methods: ['POST'])]
    public function reset(Request $request, PostTeachingResetHandler $handler): JsonResponse
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
