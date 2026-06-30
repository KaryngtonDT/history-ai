<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PipelineStageType',
    type: 'string',
    enum: ['speech_to_text', 'translation', 'text_to_speech', 'voice_clone', 'lip_sync', 'video_render'],
    example: 'translation',
)]
final class PipelineStageTypeSchema
{
}
