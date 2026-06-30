<?php

declare(strict_types=1);

namespace App\Domain\VideoRender;

use App\Domain\LipSync\LipSyncArtifact;

interface VideoRenderProviderInterface
{
    public function render(
        LipSyncArtifact $lipSync,
        VideoRenderFormat $format,
        VideoRenderQuality $quality,
    ): FinalVideoArtifact;
}
