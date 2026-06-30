<?php

declare(strict_types=1);

namespace App\Infrastructure\VideoIntelligence;

use App\Domain\VideoIntelligence\VideoAnalyzerInput;
use App\Domain\VideoIntelligence\VideoAnalyzerInterface;
use App\Domain\VideoIntelligence\VideoIntelligence;
use App\Domain\VideoIntelligence\VideoIntelligenceId;
use App\Domain\VideoIntelligence\VideoScene;
use App\Domain\VideoIntelligence\VideoSpeaker;
use App\Domain\VideoIntelligence\VideoSpeakerCollection;

final class CompositeVideoAnalyzer implements VideoAnalyzerInterface
{
    public function __construct(
        private readonly AudioAnalyzer $audioAnalyzer,
        private readonly VisualAnalyzer $visualAnalyzer,
        private readonly SpeechAnalyzer $speechAnalyzer,
    ) {
    }

    public function analyze(VideoAnalyzerInput $input): VideoIntelligence
    {
        $audio = $this->audioAnalyzer->analyze($input);
        $visual = $this->visualAnalyzer->analyze($input, $audio->speakerCount());
        $speech = $this->speechAnalyzer->analyze($input);

        return VideoIntelligence::create(
            VideoIntelligenceId::generate(),
            $input->durationSeconds(),
            $this->resolveScene($input, $audio->speakerCount(), $visual->faceCount()),
            $audio,
            $visual,
            $speech,
            $this->buildSpeakers($audio->speakerCount()),
            $input->gpuAvailable(),
            $input->estimatedVramGb(),
        );
    }

    private function resolveScene(VideoAnalyzerInput $input, int $speakerCount, int $faceCount): VideoScene
    {
        if ($faceCount >= 3 || $speakerCount >= 3) {
            return VideoScene::Conversation;
        }

        if ($input->hasSlidesHint() && $faceCount <= 1) {
            return VideoScene::Presentation;
        }

        if ($speakerCount >= 2) {
            return VideoScene::Interview;
        }

        if ($input->durationSeconds() >= 1800) {
            return VideoScene::Lecture;
        }

        if ($input->durationSeconds() >= 600) {
            return VideoScene::Podcast;
        }

        return VideoScene::Other;
    }

    private function buildSpeakers(int $speakerCount): VideoSpeakerCollection
    {
        if ($speakerCount <= 0) {
            return VideoSpeakerCollection::empty();
        }

        $speakers = [];

        for ($index = 1; $index <= $speakerCount; ++$index) {
            $speakers[] = VideoSpeaker::create($index, sprintf('Speaker %d', $index));
        }

        return new VideoSpeakerCollection($speakers);
    }
}
