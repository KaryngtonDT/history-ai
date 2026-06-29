<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Video\Commands\UploadVideoCommand;
use App\Application\Video\Handlers\UploadVideoHandler;
use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Presentation\Http\Response\Video\UploadVideoResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UploadVideoController extends AbstractController
{
    #[Route('/api/videos', name: 'api_videos_upload', methods: ['POST'])]
    public function __invoke(Request $request, UploadVideoHandler $handler): JsonResponse
    {
        $uploadedFile = $request->files->get('video');

        if (!$uploadedFile instanceof UploadedFile || !$uploadedFile->isValid()) {
            return $this->invalidRequestResponse();
        }

        try {
            $result = $handler(new UploadVideoCommand(
                originalFilename: (string) $uploadedFile->getClientOriginalName(),
                fileSizeBytes: (int) $uploadedFile->getSize(),
            ));
        } catch (InvalidVideoJobException) {
            return $this->invalidRequestResponse();
        }

        return $this->json(
            UploadVideoResponse::fromResult($result)->toArray(),
            Response::HTTP_CREATED,
        );
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
