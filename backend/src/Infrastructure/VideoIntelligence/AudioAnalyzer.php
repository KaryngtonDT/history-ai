<?php

declare(strict_types=1);

namespace App\Infrastructure\VideoIntelligence;

use App\Domain\VideoIntelligence\AudioCharacteristics;
use App\Domain\VideoIntelligence\AudioNoiseLevel;
use App\Domain\VideoIntelligence\BackgroundMusic;
use App\Domain\VideoIntelligence\SpeechConfidence;
use App\Domain\VideoIntelligence\SpeechSpeed;
use App\Domain\VideoIntelligence\VideoAnalyzerInput;

final class AudioAnalyzer
{
    public function analyze(VideoAnalyzerInput $input): AudioCharacteristics
    {
        $speakerCount = $this->estimateSpeakerCount($input);
        $wordCount = $this->wordCount($input->transcriptText());
        $wordsPerMinute = $input->durationSeconds() > 0
            ? ($wordCount / $input->durationSeconds()) * 60
            : 0.0;

        return AudioCharacteristics::create(
            $input->language(),
            $speakerCount,
            $this->resolveNoiseLevel($input),
            $this->resolveBackgroundMusic($input),
            $this->resolveSpeechSpeed($wordsPerMinute),
            SpeechConfidence::create($this->estimateConfidence($input, $wordCount)),
        );
    }

    private function estimateSpeakerCount(VideoAnalyzerInput $input): int
    {
        if ($input->segmentCount() >= 60) {
            return 3;
        }

        if ($input->segmentCount() >= 40) {
            return 2;
        }

        if ($input->segmentCount() >= 20) {
            return max(1, min(2, (int) ceil($input->segmentCount() / 15)));
        }

        return 1;
    }

    private function resolveNoiseLevel(VideoAnalyzerInput $input): AudioNoiseLevel
    {
        if ($input->segmentCount() < 5 && $input->durationSeconds() > 120) {
            return AudioNoiseLevel::High;
        }

        if ($input->segmentCount() < 10) {
            return AudioNoiseLevel::Medium;
        }

        if ($input->segmentCount() < 20) {
            return AudioNoiseLevel::Low;
        }

        return AudioNoiseLevel::None;
    }

    private function resolveBackgroundMusic(VideoAnalyzerInput $input): BackgroundMusic
    {
        $text = strtolower($input->transcriptText());

        if (str_contains($text, '[music]') || str_contains($text, 'background music')) {
            return BackgroundMusic::Detected;
        }

        if ($input->segmentCount() > 0 && $this->wordCount($input->transcriptText()) < ($input->durationSeconds() / 4)) {
            return BackgroundMusic::Detected;
        }

        return BackgroundMusic::NotDetected;
    }

    private function resolveSpeechSpeed(float $wordsPerMinute): SpeechSpeed
    {
        if ($wordsPerMinute >= 170) {
            return SpeechSpeed::Fast;
        }

        if ($wordsPerMinute <= 110) {
            return SpeechSpeed::Slow;
        }

        return SpeechSpeed::Normal;
    }

    private function estimateConfidence(VideoAnalyzerInput $input, int $wordCount): int
    {
        if (0 === $input->segmentCount()) {
            return 60;
        }

        $base = min(99, 70 + (int) floor($input->segmentCount() / 2));
        $wordBonus = min(10, (int) floor($wordCount / 50));

        return min(100, $base + $wordBonus);
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
