<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Collection;

use App\Application\Collection\Handlers\ListCollectionsHandler;
use App\Application\Collection\Queries\ListCollectionsQuery;
use App\Presentation\Http\Response\Collection\ListCollectionsResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListCollectionsController extends AbstractController
{
    #[Route('/api/collections', name: 'api_collections_list', methods: ['GET'])]
    public function __invoke(ListCollectionsHandler $handler): JsonResponse
    {
        $result = $handler(new ListCollectionsQuery());

        return $this->json(ListCollectionsResponse::fromResult($result));
    }
}
