<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowRelationship;

use App\Application\ShadowRelationship\Handlers\ResetRelationshipProfileHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PostRelationshipResetController extends AbstractController
{
    #[Route('/api/shadow/relationship/reset', name: 'api_shadow_relationship_reset', methods: ['POST'])]
    public function __invoke(Request $request, ResetRelationshipProfileHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) && is_string($payload['scopeKey'] ?? null)
            ? $payload['scopeKey']
            : (is_string($request->query->get('scopeKey')) ? $request->query->get('scopeKey') : 'default');

        return $this->json($handler($scopeKey));
    }
}
