<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowRelationship;

use App\Application\ShadowRelationship\Handlers\GetRelationshipPortraitHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetRelationshipPortraitController extends AbstractController
{
    #[Route('/api/shadow/relationship/portrait', name: 'api_shadow_relationship_portrait', methods: ['GET'])]
    public function __invoke(Request $request, GetRelationshipPortraitHandler $handler): JsonResponse
    {
        $scopeKey = is_string($request->query->get('scopeKey')) ? $request->query->get('scopeKey') : 'default';

        return $this->json($handler($scopeKey));
    }
}
