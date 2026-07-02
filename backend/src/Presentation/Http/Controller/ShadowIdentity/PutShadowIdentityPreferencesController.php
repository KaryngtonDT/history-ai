<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowIdentity;

use App\Application\ShadowIdentity\Handlers\PutShadowIdentityPreferencesHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PutShadowIdentityPreferencesController extends AbstractController
{
    #[Route('/api/shadow/identity/preferences', name: 'api_shadow_identity_preferences', methods: ['PUT'])]
    public function __invoke(Request $request, PutShadowIdentityPreferencesHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], 400);
        }

        $scopeKey = is_string($payload['scopeKey'] ?? null) ? $payload['scopeKey'] : 'default';

        return $this->json($handler($payload, $scopeKey));
    }
}
