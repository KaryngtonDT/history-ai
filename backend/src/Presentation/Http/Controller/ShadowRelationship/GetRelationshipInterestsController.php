<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowRelationship;

use App\Application\ShadowRelationship\Handlers\GetRelationshipInterestsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetRelationshipInterestsController extends AbstractController
{
    #[Route('/api/shadow/relationship/interests', name: 'api_shadow_relationship_interests', methods: ['GET'])]
    public function __invoke(Request $request, GetRelationshipInterestsHandler $handler): JsonResponse
    {
        $scopeKey = is_string($request->query->get('scopeKey')) ? $request->query->get('scopeKey') : 'default';

        return $this->json($handler($scopeKey));
    }
}
