<?php

declare(strict_types=1);

namespace App\Infrastructure\VideoIntelligence;

use App\Domain\VideoIntelligence\LightingCondition;
use App\Domain\VideoIntelligence\LipVisibility;
use App\Domain\VideoIntelligence\VideoAnalyzerInput;
use App\Domain\VideoIntelligence\VisualCharacteristics;

final class VisualAnalyzer
{
    public function analyze(VideoAnalyzerInput $input, int $speakerCount): VisualCharacteristics
    {
        $faceCount = max($speakerCount, 1);

        return VisualCharacteristics::create(
            $input->resolution(),
            $input->fps(),
            $this->resolveLighting($input),
            $this->resolveLipVisibility($input),
            $faceCount,
        );
    }

    private function resolveLighting(VideoAnalyzerInput $input): LightingCondition
    {
        if (str_starts_with($input->resolution(), '3840') || str_starts_with($input->resolution(), '1920')) {
            return LightingCondition::Good;
        }

        if (str_starts_with($input->resolution(), '1280')) {
            return LightingCondition::Average;
        }

        return LightingCondition::Poor;
    }

    private function resolveLipVisibility(VideoAnalyzerInput $input): LipVisibility
    {
        if ($input->fps() >= 30 && str_starts_with($input->resolution(), '1920')) {
            return LipVisibility::Excellent;
        }

        if ($input->fps() >= 24) {
            return LipVisibility::Good;
        }

        if ($input->fps() >= 20) {
            return LipVisibility::Partial;
        }

        return LipVisibility::Poor;
    }
}
