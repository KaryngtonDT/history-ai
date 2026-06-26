<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Content;

use App\Application\Content\Handlers\ListContentsHandler;
use App\Application\Content\Queries\ListContentsQuery;
use App\Presentation\Http\Response\Content\ListContentsResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListContentsController extends AbstractController
{
    #[Route('/api/contents', name: 'api_contents_list', methods: ['GET'])]
    public function __invoke(ListContentsHandler $handler): JsonResponse
    {
        $result = $handler(new ListContentsQuery());

        return $this->json(ListContentsResponse::fromResult($result));
    }
}
