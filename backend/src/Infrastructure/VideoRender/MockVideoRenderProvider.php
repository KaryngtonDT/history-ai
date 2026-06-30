<?php

declare(strict_types=1);

namespace App\Infrastructure\VideoRender;

use App\Domain\LipSync\LipSyncArtifact;
use App\Domain\VideoRender\FinalVideoArtifact;
use App\Domain\VideoRender\FinalVideoId;
use App\Domain\VideoRender\VideoRenderFormat;
use App\Domain\VideoRender\VideoRenderProvider;
use App\Domain\VideoRender\VideoRenderProviderInterface;
use App\Domain\VideoRender\VideoRenderQuality;

final class MockVideoRenderProvider implements VideoRenderProviderInterface
{
    public function render(
        LipSyncArtifact $lipSync,
        VideoRenderFormat $format,
        VideoRenderQuality $quality,
    ): FinalVideoArtifact {
        return FinalVideoArtifact::create(
            FinalVideoId::generate(),
            $lipSync->sourceVideoId(),
            $lipSync->artifactId(),
            VideoRenderProvider::Mock,
            $format,
            $quality,
            max(1.0, $lipSync->video()->duration()),
            2048,
        );
    }
}
