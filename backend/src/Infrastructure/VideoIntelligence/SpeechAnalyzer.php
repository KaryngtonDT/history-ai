<?php

declare(strict_types=1);

namespace App\Infrastructure\VideoIntelligence;

use App\Domain\VideoIntelligence\SpeechCharacteristics;
use App\Domain\VideoIntelligence\VideoAnalyzerInput;
use App\Domain\VideoIntelligence\VideoEmotion;

final class SpeechAnalyzer
{
    public function analyze(VideoAnalyzerInput $input): SpeechCharacteristics
    {
        $wordCount = $this->wordCount($input->transcriptText());
        $wordsPerMinute = $input->durationSeconds() > 0
            ? ($wordCount / $input->durationSeconds()) * 60
            : 0.0;

        return SpeechCharacteristics::create(
            $this->resolveEmotion($input->transcriptText()),
            $wordsPerMinute,
            max(0, $input->segmentCount() - 1),
            $input->segmentCount() > 30 && $input->durationSeconds() < 300,
        );
    }

    private function resolveEmotion(string $text): VideoEmotion
    {
        $normalized = strtolower($text);

        if (str_contains($normalized, 'excited') || str_contains($normalized, 'amazing')) {
            return VideoEmotion::Excited;
        }

        if (str_contains($normalized, 'happy') || str_contains($normalized, 'great')) {
            return VideoEmotion::Happy;
        }

        if (str_contains($normalized, 'sad') || str_contains($normalized, 'sorry')) {
            return VideoEmotion::Sad;
        }

        if (str_contains($normalized, 'angry') || str_contains($normalized, 'frustrated')) {
            return VideoEmotion::Angry;
        }

        return VideoEmotion::Neutral;
    }

    private function wordCount(string $text): int
    {
        $trimmed = trim($text);

        if ('' === $trimmed) {
            return 0;
        }

        return count(preg_split('/\s+/', $trimmed) ?: []);
    }
}
