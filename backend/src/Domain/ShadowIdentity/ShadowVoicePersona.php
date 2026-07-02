<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

enum ShadowVoicePersona: string
{
    case Teacher = 'teacher';
    case Professor = 'professor';
    case UniversityLecturer = 'university_lecturer';
    case TechnicalExpert = 'technical_expert';
    case Coach = 'coach';
    case FriendlyCompanion = 'friendly_companion';
    case Storyteller = 'storyteller';
    case DocumentaryNarrator = 'documentary_narrator';
    case Historian = 'historian';
    case Philosopher = 'philosopher';
    case Scientist = 'scientist';
    case BusinessConsultant = 'business_consultant';
    case Debater = 'debater';
    case SocraticMentor = 'socratic_mentor';

    public function traits(): ShadowPersonaTraits
    {
        return match ($this) {
            self::Teacher => new ShadowPersonaTraits(6, 6, 8, 6, 8, 6, 5, 3, 6, 5, ShadowThinkingStyle::Structured, 6),
            self::Professor => new ShadowPersonaTraits(7, 5, 6, 8, 7, 7, 6, 2, 5, 4, ShadowThinkingStyle::Analytical, 5),
            self::UniversityLecturer => new ShadowPersonaTraits(7, 6, 5, 9, 7, 6, 6, 2, 4, 3, ShadowThinkingStyle::Structured, 4),
            self::TechnicalExpert => new ShadowPersonaTraits(6, 5, 4, 7, 8, 5, 7, 1, 3, 2, ShadowThinkingStyle::Analytical, 5),
            self::Coach => new ShadowPersonaTraits(7, 8, 9, 5, 6, 5, 7, 4, 8, 4, ShadowThinkingStyle::Exploratory, 7),
            self::FriendlyCompanion => new ShadowPersonaTraits(5, 7, 10, 5, 5, 4, 3, 6, 9, 5, ShadowThinkingStyle::Intuitive, 8),
            self::Storyteller => new ShadowPersonaTraits(6, 7, 8, 7, 6, 7, 3, 5, 9, 10, ShadowThinkingStyle::Intuitive, 7),
            self::DocumentaryNarrator => new ShadowPersonaTraits(6, 4, 5, 7, 5, 4, 2, 1, 6, 9, ShadowThinkingStyle::Structured, 4),
            self::Historian => new ShadowPersonaTraits(7, 5, 6, 8, 7, 8, 4, 2, 7, 8, ShadowThinkingStyle::Analytical, 5),
            self::Philosopher => new ShadowPersonaTraits(6, 4, 5, 8, 5, 6, 5, 2, 6, 4, ShadowThinkingStyle::Socratic, 4),
            self::Scientist => new ShadowPersonaTraits(6, 5, 4, 7, 9, 6, 6, 1, 3, 2, ShadowThinkingStyle::Analytical, 5),
            self::BusinessConsultant => new ShadowPersonaTraits(7, 7, 6, 6, 7, 5, 5, 3, 5, 3, ShadowThinkingStyle::Structured, 6),
            self::Debater => new ShadowPersonaTraits(7, 8, 4, 7, 6, 5, 8, 4, 5, 3, ShadowThinkingStyle::Analytical, 8),
            self::SocraticMentor => new ShadowPersonaTraits(6, 5, 7, 6, 5, 4, 7, 2, 5, 3, ShadowThinkingStyle::Socratic, 5),
        };
    }

    public function defaultConversationStyle(): ShadowConversationStyle
    {
        return match ($this) {
            self::FriendlyCompanion => ShadowConversationStyle::Friendly,
            self::Debater => ShadowConversationStyle::Debate,
            self::SocraticMentor, self::Philosopher => ShadowConversationStyle::Socratic,
            self::Coach => ShadowConversationStyle::Coach,
            self::Professor, self::UniversityLecturer => ShadowConversationStyle::Academic,
            default => ShadowConversationStyle::Conversational,
        };
    }

    public function defaultNarrationStyle(): ShadowNarrationStyle
    {
        return match ($this) {
            self::Storyteller => ShadowNarrationStyle::Storytelling,
            self::DocumentaryNarrator => ShadowNarrationStyle::Documentary,
            self::Professor, self::UniversityLecturer => ShadowNarrationStyle::Professor,
            self::Coach => ShadowNarrationStyle::Coach,
            self::FriendlyCompanion => ShadowNarrationStyle::Friendly,
            self::Debater => ShadowNarrationStyle::Debate,
            self::SocraticMentor => ShadowNarrationStyle::Socratic,
            default => ShadowNarrationStyle::Neutral,
        };
    }

    public function defaultTeachingStyle(): ShadowTeachingStyle
    {
        return match ($this) {
            self::Storyteller, self::Historian => ShadowTeachingStyle::StoryBased,
            self::Debater => ShadowTeachingStyle::Debate,
            self::Coach => ShadowTeachingStyle::Exercise,
            self::TechnicalExpert, self::Scientist => ShadowTeachingStyle::PrincipleFirst,
            default => ShadowTeachingStyle::ExampleFirst,
        };
    }
}
