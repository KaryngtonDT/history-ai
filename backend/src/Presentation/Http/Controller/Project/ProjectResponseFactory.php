<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Project;

use App\Application\Workspace\DTO\ProjectResult;
use App\Application\Workspace\DTO\ProjectVideoResult;

final class ProjectResponseFactory
{
    /**
     * @return array<string, mixed>
     */
    public static function fromResult(ProjectResult $result): array
    {
        return [
            'id' => $result->id,
            'name' => $result->name,
            'createdAt' => $result->createdAt,
            'videos' => array_map(
                static fn (ProjectVideoResult $video): array => [
                    'videoId' => $video->videoId,
                    'filename' => $video->filename,
                    'addedAt' => $video->addedAt,
                ],
                $result->videos,
            ),
            'batchJobId' => $result->batchJobId,
            'batchStatus' => $result->batchStatus,
            'batchProgress' => $result->batchProgress,
            'targetLanguages' => $result->targetLanguages,
        ];
    }
}
