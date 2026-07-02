<?php

declare(strict_types=1);

namespace App\Application\ShadowVoice;

enum ShadowVoiceCollection: string
{
    case GreatStorytellers = 'great_storytellers';
    case DocumentaryNarrators = 'documentary_narrators';
    case UniversityProfessors = 'university_professors';
    case TechnicalExperts = 'technical_experts';
    case FriendlyCompanions = 'friendly_companions';
    case BusinessSpeakers = 'business_speakers';
    case DebateMasters = 'debate_masters';
    case SocraticMentors = 'socratic_mentors';

    public function label(): string
    {
        return match ($this) {
            self::GreatStorytellers => 'Great Storytellers',
            self::DocumentaryNarrators => 'Documentary Narrators',
            self::UniversityProfessors => 'University Professors',
            self::TechnicalExperts => 'Technical Experts',
            self::FriendlyCompanions => 'Friendly Companions',
            self::BusinessSpeakers => 'Business Speakers',
            self::DebateMasters => 'Debate Masters',
            self::SocraticMentors => 'Socratic Mentors',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::GreatStorytellers => 'Warm narrative voices for storytelling and history content.',
            self::DocumentaryNarrators => 'Calm, authoritative voices inspired by documentary narration.',
            self::UniversityProfessors => 'Clear academic voices for lectures and explanations.',
            self::TechnicalExperts => 'Precise voices for technical and scientific content.',
            self::FriendlyCompanions => 'Approachable voices for casual learning conversations.',
            self::BusinessSpeakers => 'Confident voices for professional and business topics.',
            self::DebateMasters => 'Energetic voices for debate and argumentation.',
            self::SocraticMentors => 'Reflective voices for guided questioning and mentoring.',
        };
    }
}
