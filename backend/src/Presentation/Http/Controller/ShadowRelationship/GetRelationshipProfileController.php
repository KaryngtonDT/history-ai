<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowRelationship;

use App\Application\ShadowRelationship\Handlers\GetRelationshipProfileHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetRelationshipProfileController extends AbstractController
{
    #[Route('/api/shadow/relationship/profile', name: 'api_shadow_relationship_profile', methods: ['GET'])]
    public function __invoke(Request $request, GetRelationshipProfileHandler $handler): JsonResponse
    {
        $scopeKey = is_string($request->query->get('scopeKey')) ? $request->query->get('scopeKey') : 'default';

        return $this->json($handler($scopeKey));
    }
}
