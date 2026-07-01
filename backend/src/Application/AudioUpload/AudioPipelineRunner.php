<?php

declare(strict_types=1);

namespace App\Application\AudioUpload;

final class AudioPipelineRunner
{
    /**
     * @param callable(): void $transcriptStage
     * @param callable(): void $translationStage
     */
    public function run(
        AudioProcessingContext $context,
        callable $transcriptStage,
        callable $translationStage,
    ): void {
        $transcriptStage();
        $translationStage();
    }
}
