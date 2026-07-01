<?php

declare(strict_types=1);

namespace App\Application\Learning;

use App\Domain\Learning\LearningInsight;
use App\Domain\Learning\LearningInsightCollection;
use App\Domain\Learning\LearningInsightType;
use App\Domain\Learning\LearningSignal;
use App\Domain\Learning\LearningSignalCollection;
use App\Domain\Learning\LearningSignalType;

final class LearningInsightGenerator
{
    private const int MIN_PATTERN_COUNT = 3;
    private const int MIN_PAUSE_PATTERN_COUNT = 5;
    private const int MIN_PROVIDER_OBSERVATIONS = 2;
    private const int QUALITY_RISK_THRESHOLD = 75;

    public function generate(LearningSignalCollection $signals): LearningInsightCollection
    {
        $collection = LearningInsightCollection::empty();

        foreach ($this->buildInsights($signals) as $insight) {
            $collection = $collection->append($insight);
        }

        return $collection;
    }

    /**
     * @return list<LearningInsight>
     */
    private function buildInsights(LearningSignalCollection $signals): array
    {
        $insights = [];

        $vocabularyInsight = $this->vocabularyGap($signals);

        if (null !== $vocabularyInsight) {
            $insights[] = $vocabularyInsight;
        }

        $grammarInsight = $this->grammarGap($signals);

        if (null !== $grammarInsight) {
            $insights[] = $grammarInsight;
        }

        $explanationInsight = $this->preferredExplanationStyle($signals);

        if (null !== $explanationInsight) {
            $insights[] = $explanationInsight;
        }

        $translationInsight = $this->preferredTranslationStyle($signals);

        if (null !== $translationInsight) {
            $insights[] = $translationInsight;
        }

        $voiceInsight = $this->preferredVoiceLanguage($signals);

        if (null !== $voiceInsight) {
            $insights[] = $voiceInsight;
        }

        $challengeInsight = $this->preferredChallengeLevel($signals);

        if (null !== $challengeInsight) {
            $insights[] = $challengeInsight;
        }

        $topicInsight = $this->frequentTopic($signals);

        if (null !== $topicInsight) {
            $insights[] = $topicInsight;
        }

        $providerInsight = $this->providerPreference($signals);

        if (null !== $providerInsight) {
            $insights[] = $providerInsight;
        }

        $qualityInsight = $this->qualityRiskPattern($signals);

        if (null !== $qualityInsight) {
            $insights[] = $qualityInsight;
        }

        $paceInsight = $this->pacePreference($signals);

        if (null !== $paceInsight) {
            $insights[] = $paceInsight;
        }

        return $insights;
    }

    private function vocabularyGap(LearningSignalCollection $signals): ?LearningInsight
    {
        $vocabularyQuestions = array_filter(
            $signals->ofType(LearningSignalType::ShadowQuestionAsked)->all(),
            static fn (LearningSignal $signal): bool => 'vocabulary' === ($signal->context()['questionType'] ?? ''),
        );
        $repeated = $signals->ofType(LearningSignalType::RepeatedVocabulary)->all();
        $combined = [...$vocabularyQuestions, ...$repeated];

        if (count($combined) < self::MIN_PATTERN_COUNT) {
            return null;
        }

        return LearningInsight::derive(
            LearningInsightType::VocabularyGap,
            sprintf(
                'Observed %d vocabulary-related signals, indicating recurring word-meaning questions.',
                count($combined),
            ),
            $this->signalIds($combined),
        );
    }

    private function grammarGap(LearningSignalCollection $signals): ?LearningInsight
    {
        $grammarSignals = $signals->ofType(LearningSignalType::GrammarDifficulty)->all();

        if (count($grammarSignals) < self::MIN_PATTERN_COUNT) {
            return null;
        }

        return LearningInsight::derive(
            LearningInsightType::GrammarGap,
            sprintf(
                'Observed %d grammar difficulty signals across recent Shadow interactions.',
                count($grammarSignals),
            ),
            $this->signalIds($grammarSignals),
        );
    }

    private function preferredExplanationStyle(LearningSignalCollection $signals): ?LearningInsight
    {
        $depthSignals = $signals->ofType(LearningSignalType::ExplanationDepthPreference)->all();

        if ([] === $depthSignals) {
            return null;
        }

        $counts = ['short' => 0, 'detailed' => 0];

        foreach ($depthSignals as $signal) {
            $depth = is_string($signal->context()['depth'] ?? null) ? $signal->context()['depth'] : '';

            if (isset($counts[$depth])) {
                ++$counts[$depth];
            }
        }

        if ($counts['short'] >= self::MIN_PATTERN_COUNT && $counts['short'] >= $counts['detailed']) {
            return LearningInsight::derive(
                LearningInsightType::PreferredExplanationStyle,
                sprintf(
                    'User preferred short explanations in %d of %d depth signals.',
                    $counts['short'],
                    count($depthSignals),
                ),
                $this->signalIds($depthSignals),
            );
        }

        if ($counts['detailed'] >= self::MIN_PATTERN_COUNT) {
            return LearningInsight::derive(
                LearningInsightType::PreferredExplanationStyle,
                sprintf(
                    'User preferred detailed explanations in %d of %d depth signals.',
                    $counts['detailed'],
                    count($depthSignals),
                ),
                $this->signalIds($depthSignals),
            );
        }

        return null;
    }

    private function preferredTranslationStyle(LearningSignalCollection $signals): ?LearningInsight
    {
        $styleSignals = $signals->ofType(LearningSignalType::TranslationStylePreference)->all();

        if ([] === $styleSignals) {
            return null;
        }

        $counts = ['literal' => 0, 'natural' => 0];

        foreach ($styleSignals as $signal) {
            $style = is_string($signal->context()['style'] ?? null) ? $signal->context()['style'] : '';

            if (isset($counts[$style])) {
                ++$counts[$style];
            }
        }

        if ($counts['literal'] >= self::MIN_PATTERN_COUNT && $counts['literal'] >= $counts['natural']) {
            return LearningInsight::derive(
                LearningInsightType::PreferredTranslationStyle,
                sprintf(
                    'Literal translation style appeared in %d of %d preference signals.',
                    $counts['literal'],
                    count($styleSignals),
                ),
                $this->signalIds($styleSignals),
            );
        }

        if ($counts['natural'] >= self::MIN_PATTERN_COUNT) {
            return LearningInsight::derive(
                LearningInsightType::PreferredTranslationStyle,
                sprintf(
                    'Natural translation style appeared in %d of %d preference signals.',
                    $counts['natural'],
                    count($styleSignals),
                ),
                $this->signalIds($styleSignals),
            );
        }

        return null;
    }

    private function preferredVoiceLanguage(LearningSignalCollection $signals): ?LearningInsight
    {
        $voiceSignals = $signals->ofType(LearningSignalType::VoiceLanguagePreference)->all();

        if ([] === $voiceSignals) {
            return null;
        }

        $counts = [];

        foreach ($voiceSignals as $signal) {
            $language = is_string($signal->context()['language'] ?? null) ? $signal->context()['language'] : 'en';
            $counts[$language] = ($counts[$language] ?? 0) + 1;
        }

        arsort($counts);
        $topLanguage = array_key_first($counts);
        $topCount = $counts[$topLanguage] ?? 0;

        if ($topCount < self::MIN_PATTERN_COUNT) {
            return null;
        }

        return LearningInsight::derive(
            LearningInsightType::PreferredVoiceLanguage,
            sprintf(
                'Voice language "%s" selected in %d of %d voice preference signals.',
                $topLanguage,
                $topCount,
                count($voiceSignals),
            ),
            $this->signalIds($voiceSignals),
        );
    }

    private function preferredChallengeLevel(LearningSignalCollection $signals): ?LearningInsight
    {
        $skippedHard = array_filter(
            $signals->ofType(LearningSignalType::ShadowInterventionSkipped)->all(),
            static fn (LearningSignal $signal): bool => 'hard' === ($signal->context()['difficulty'] ?? ''),
        );
        $failedHard = array_filter(
            $signals->ofType(LearningSignalType::ShadowChallengeFailed)->all(),
            static fn (LearningSignal $signal): bool => 'hard' === ($signal->context()['difficulty'] ?? ''),
        );
        $easySuccess = array_filter(
            $signals->ofType(LearningSignalType::ShadowChallengeAnswered)->all(),
            static fn (LearningSignal $signal): bool => 'easy' === ($signal->context()['difficulty'] ?? '')
                && true === ($signal->context()['correct'] ?? false),
        );

        $hardStruggleCount = count($skippedHard) + count($failedHard);

        if ($hardStruggleCount >= self::MIN_PATTERN_COUNT) {
            return LearningInsight::derive(
                LearningInsightType::PreferredChallengeLevel,
                sprintf(
                    'User skipped or failed %d hard challenges, suggesting a lower challenge level.',
                    $hardStruggleCount,
                ),
                $this->signalIds([...$skippedHard, ...$failedHard]),
            );
        }

        if (count($easySuccess) >= self::MIN_PATTERN_COUNT) {
            return LearningInsight::derive(
                LearningInsightType::PreferredChallengeLevel,
                sprintf(
                    'User answered %d easy challenges correctly, suggesting a higher challenge level.',
                    count($easySuccess),
                ),
                $this->signalIds($easySuccess),
            );
        }

        return null;
    }

    private function frequentTopic(LearningSignalCollection $signals): ?LearningInsight
    {
        $topicSignals = $signals->ofType(LearningSignalType::TopicInterestObserved)->all();

        if ([] === $topicSignals) {
            return null;
        }

        $counts = [];

        foreach ($topicSignals as $signal) {
            $topic = is_string($signal->context()['topic'] ?? null) ? $signal->context()['topic'] : 'general';
            $counts[$topic] = ($counts[$topic] ?? 0) + 1;
        }

        arsort($counts);
        $topTopic = array_key_first($counts);
        $topCount = $counts[$topTopic] ?? 0;

        if ($topCount < self::MIN_PATTERN_COUNT) {
            return null;
        }

        return LearningInsight::derive(
            LearningInsightType::FrequentTopic,
            sprintf('Topic "%s" appeared in %d interest signals.', $topTopic, $topCount),
            $this->signalIds($topicSignals),
        );
    }

    private function providerPreference(LearningSignalCollection $signals): ?LearningInsight
    {
        $providerSignals = $signals->ofType(LearningSignalType::ProviderPerformanceObserved)->all();

        if (count($providerSignals) < self::MIN_PROVIDER_OBSERVATIONS) {
            return null;
        }

        $scores = [];

        foreach ($providerSignals as $signal) {
            $providerId = is_string($signal->context()['providerId'] ?? null) ? $signal->context()['providerId'] : '';
            $qualityScore = $signal->context()['qualityScore'] ?? $signal->context()['value'] ?? null;

            if ('' === $providerId || !is_numeric($qualityScore)) {
                continue;
            }

            $scores[$providerId][] = (int) $qualityScore;
        }

        if ([] === $scores) {
            $successfulProviders = array_filter(
                $providerSignals,
                static fn (LearningSignal $signal): bool => true === ($signal->context()['success'] ?? false),
            );

            if (count($successfulProviders) < self::MIN_PROVIDER_OBSERVATIONS) {
                return null;
            }

            $counts = [];

            foreach ($successfulProviders as $signal) {
                $providerId = is_string($signal->context()['providerId'] ?? null) ? $signal->context()['providerId'] : '';
                $counts[$providerId] = ($counts[$providerId] ?? 0) + 1;
            }

            arsort($counts);
            $topProvider = array_key_first($counts);

            return LearningInsight::derive(
                LearningInsightType::ProviderPreference,
                sprintf('Provider "%s" succeeded most often in recent telemetry.', $topProvider),
                $this->signalIds($successfulProviders),
            );
        }

        $averages = [];

        foreach ($scores as $providerId => $values) {
            $averages[$providerId] = array_sum($values) / count($values);
        }

        arsort($averages);
        $topProvider = array_key_first($averages);
        $topAverage = $averages[$topProvider] ?? 0.0;

        return LearningInsight::derive(
            LearningInsightType::ProviderPreference,
            sprintf(
                'Provider "%s" averaged quality score %.1f across %d observations.',
                $topProvider,
                $topAverage,
                count($scores[$topProvider] ?? []),
            ),
            $this->signalIds($providerSignals),
        );
    }

    private function qualityRiskPattern(LearningSignalCollection $signals): ?LearningInsight
    {
        $qualitySignals = array_filter(
            $signals->ofType(LearningSignalType::QualityScoreObserved)->all(),
            static fn (LearningSignal $signal): bool => is_numeric($signal->context()['value'] ?? null)
                && (int) $signal->context()['value'] < self::QUALITY_RISK_THRESHOLD,
        );

        if (count($qualitySignals) < self::MIN_PROVIDER_OBSERVATIONS) {
            return null;
        }

        return LearningInsight::derive(
            LearningInsightType::QualityRiskPattern,
            sprintf(
                'Observed %d quality scores below %d, indicating recurring quality risk.',
                count($qualitySignals),
                self::QUALITY_RISK_THRESHOLD,
            ),
            $this->signalIds($qualitySignals),
        );
    }

    private function pacePreference(LearningSignalCollection $signals): ?LearningInsight
    {
        $pauseSignals = array_filter(
            $signals->ofType(LearningSignalType::PlaybackPausePattern)->all(),
            static fn (LearningSignal $signal): bool => 'technical' === ($signal->context()['segmentType'] ?? ''),
        );

        if (count($pauseSignals) < self::MIN_PAUSE_PATTERN_COUNT) {
            return null;
        }

        return LearningInsight::derive(
            LearningInsightType::PacePreference,
            sprintf(
                'User paused %d times during technical segments, suggesting slower pacing.',
                count($pauseSignals),
            ),
            $this->signalIds($pauseSignals),
        );
    }

    /**
     * @param list<LearningSignal> $signals
     *
     * @return list<string>
     */
    private function signalIds(array $signals): array
    {
        return array_values(array_map(
            static fn (LearningSignal $signal): string => $signal->id()->value,
            $signals,
        ));
    }
}
