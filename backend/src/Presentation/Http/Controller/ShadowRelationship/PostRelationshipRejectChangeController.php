<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowRelationship;

use App\Application\ShadowRelationship\Handlers\ResolveRelationshipPendingChangeHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PostRelationshipRejectChangeController extends AbstractController
{
    #[Route('/api/shadow/relationship/changes/{changeId}/reject', name: 'api_shadow_relationship_change_reject', methods: ['POST'])]
    public function __invoke(
        string $changeId,
        Request $request,
        ResolveRelationshipPendingChangeHandler $handler,
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) && is_string($payload['scopeKey'] ?? null)
            ? $payload['scopeKey']
            : 'default';

        return $this->json($handler->reject($scopeKey, $changeId));
    }
}
