<?php

declare(strict_types=1);

namespace App\Domain\Learning;

enum LearningSignalType: string
{
    case ShadowQuestionAsked = 'shadow_question_asked';
    case ShadowInterventionShown = 'shadow_intervention_shown';
    case ShadowInterventionSkipped = 'shadow_intervention_skipped';
    case ShadowChallengeAnswered = 'shadow_challenge_answered';
    case ShadowChallengeFailed = 'shadow_challenge_failed';
    case RepeatedVocabulary = 'repeated_vocabulary';
    case GrammarDifficulty = 'grammar_difficulty';
    case TranslationStylePreference = 'translation_style_preference';
    case ExplanationDepthPreference = 'explanation_depth_preference';
    case VoiceLanguagePreference = 'voice_language_preference';
    case PlaybackPausePattern = 'playback_pause_pattern';
    case QualityScoreObserved = 'quality_score_observed';
    case UserReviewSubmitted = 'user_review_submitted';
    case ProviderPerformanceObserved = 'provider_performance_observed';
    case TopicInterestObserved = 'topic_interest_observed';
}
