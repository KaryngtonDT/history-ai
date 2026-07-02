<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowRelationship;

use App\Application\ShadowRelationship\Handlers\UpdateRelationshipPreferencesHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PostRelationshipPreferencesController extends AbstractController
{
    #[Route('/api/shadow/relationship/preferences', name: 'api_shadow_relationship_preferences', methods: ['POST'])]
    public function __invoke(Request $request, UpdateRelationshipPreferencesHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], 400);
        }

        $scopeKey = is_string($payload['scopeKey'] ?? null) ? $payload['scopeKey'] : 'default';

        return $this->json($handler($scopeKey, $payload));
    }
}
