<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowRelationship;

use App\Application\ShadowRelationship\Handlers\RecordRelationshipSignalsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PostRelationshipSignalsController extends AbstractController
{
    #[Route('/api/shadow/relationship/signals', name: 'api_shadow_relationship_signals', methods: ['POST'])]
    public function __invoke(Request $request, RecordRelationshipSignalsHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], 400);
        }

        $scopeKey = is_string($payload['scopeKey'] ?? null) ? $payload['scopeKey'] : 'default';

        return $this->json($handler($scopeKey, $payload));
    }
}
