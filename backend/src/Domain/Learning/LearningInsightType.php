<?php

declare(strict_types=1);

namespace App\Domain\Learning;

enum LearningInsightType: string
{
    case VocabularyGap = 'vocabulary_gap';
    case GrammarGap = 'grammar_gap';
    case PreferredExplanationStyle = 'preferred_explanation_style';
    case PreferredTranslationStyle = 'preferred_translation_style';
    case PreferredVoiceLanguage = 'preferred_voice_language';
    case PreferredChallengeLevel = 'preferred_challenge_level';
    case FrequentTopic = 'frequent_topic';
    case ProviderPreference = 'provider_preference';
    case QualityRiskPattern = 'quality_risk_pattern';
    case PacePreference = 'pace_preference';
}
