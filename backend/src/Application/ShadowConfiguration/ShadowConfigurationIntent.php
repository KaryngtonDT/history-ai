<?php

declare(strict_types=1);

namespace App\Application\ShadowConfiguration;

enum ShadowConfigurationIntent: string
{
    case ChangeVoice = 'change_voice';
    case ChangePersona = 'change_persona';
    case ChangeLanguage = 'change_language';
    case ChangeTeachingStyle = 'change_teaching_style';
    case ChangeNarrationStyle = 'change_narration_style';
    case ChangeChallenge = 'change_challenge';
    case ChangeHumor = 'change_humor';
    case ChangeAnswerLength = 'change_answer_length';
    case ChangeSpeed = 'change_speed';
    case ChangeTechnicalTerms = 'change_technical_terms';
    case ChangePronunciation = 'change_pronunciation';
    case ChangeMemory = 'change_memory';
    case ForgetPreference = 'forget_preference';
    case ResetProfile = 'reset_profile';
    case UpdateLearningStyle = 'update_learning_style';
    case UpdateConversationStyle = 'update_conversation_style';
    case Unknown = 'unknown';
}
