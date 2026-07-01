<?php

declare(strict_types=1);

namespace App\Domain\Learning;

enum LearningRecommendationType: string
{
    case ExplainMoreExamples = 'explain_more_examples';
    case UseLiteralTranslation = 'use_literal_translation';
    case UseNaturalTranslation = 'use_natural_translation';
    case SlowDownPlayback = 'slow_down_playback';
    case IncreaseChallengeLevel = 'increase_challenge_level';
    case DecreaseChallengeLevel = 'decrease_challenge_level';
    case PreferVoiceLanguage = 'prefer_voice_language';
    case PreferProvider = 'prefer_provider';
    case ShowVocabularyBeforePlayback = 'show_vocabulary_before_playback';
    case UseShortExplanations = 'use_short_explanations';
    case UseDetailedExplanations = 'use_detailed_explanations';
}
