<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

final readonly class ShadowIdentityPreferences
{
    public function __construct(
        private ShadowVoicePersona $persona,
        private ShadowPersonaTraits $personaTraits,
        private ShadowVoiceProfile $voiceProfile,
        private ShadowConversationStyle $conversationStyle,
        private ShadowTeachingStyle $teachingStyle,
        private ShadowNarrationStyle $narrationStyle,
        private ShadowLanguageProfile $languageProfile,
        private ShadowAnswerStyle $answerStyle,
        private ShadowChallengeProfile $challengeProfile,
        private ShadowMemoryPolicy $memoryPolicy,
        private ShadowInterruptionPolicy $interruptionPolicy,
        private ShadowThinkingStyle $thinkingStyle,
        private ShadowHumorLevel $humorLevel,
        private int $curiosity,
        private int $examplesLevel,
        private int $storiesLevel,
        private int $debateLevel,
    ) {
    }

    public static function default(): self
    {
        $persona = ShadowVoicePersona::Teacher;
        $traits = $persona->traits();

        return new self(
            persona: $persona,
            personaTraits: $traits,
            voiceProfile: ShadowVoiceProfile::default(),
            conversationStyle: $persona->defaultConversationStyle(),
            teachingStyle: $persona->defaultTeachingStyle(),
            narrationStyle: $persona->defaultNarrationStyle(),
            languageProfile: ShadowLanguageProfile::default(),
            answerStyle: ShadowAnswerStyle::Detailed,
            challengeProfile: ShadowChallengeProfile::default(),
            memoryPolicy: ShadowMemoryPolicy::default(),
            interruptionPolicy: ShadowInterruptionPolicy::default(),
            thinkingStyle: $traits->thinkingStyle,
            humorLevel: ShadowHumorLevel::Low,
            curiosity: 6,
            examplesLevel: $traits->examples,
            storiesLevel: $traits->storytelling,
            debateLevel: 4,
        );
    }

    public function persona(): ShadowVoicePersona
    {
        return $this->persona;
    }

    public function personaTraits(): ShadowPersonaTraits
    {
        return $this->personaTraits;
    }

    public function voiceProfile(): ShadowVoiceProfile
    {
        return $this->voiceProfile;
    }

    public function conversationStyle(): ShadowConversationStyle
    {
        return $this->conversationStyle;
    }

    public function teachingStyle(): ShadowTeachingStyle
    {
        return $this->teachingStyle;
    }

    public function narrationStyle(): ShadowNarrationStyle
    {
        return $this->narrationStyle;
    }

    public function languageProfile(): ShadowLanguageProfile
    {
        return $this->languageProfile;
    }

    public function answerStyle(): ShadowAnswerStyle
    {
        return $this->answerStyle;
    }

    public function challengeProfile(): ShadowChallengeProfile
    {
        return $this->challengeProfile;
    }

    public function memoryPolicy(): ShadowMemoryPolicy
    {
        return $this->memoryPolicy;
    }

    public function interruptionPolicy(): ShadowInterruptionPolicy
    {
        return $this->interruptionPolicy;
    }

    public function thinkingStyle(): ShadowThinkingStyle
    {
        return $this->thinkingStyle;
    }

    public function humorLevel(): ShadowHumorLevel
    {
        return $this->humorLevel;
    }

    public function curiosity(): int
    {
        return $this->curiosity;
    }

    public function examplesLevel(): int
    {
        return $this->examplesLevel;
    }

    public function storiesLevel(): int
    {
        return $this->storiesLevel;
    }

    public function debateLevel(): int
    {
        return $this->debateLevel;
    }

    public function withPersona(ShadowVoicePersona $persona): self
    {
        $traits = $persona->traits();

        return new self(
            $persona,
            $traits,
            $this->voiceProfile,
            $persona->defaultConversationStyle(),
            $persona->defaultTeachingStyle(),
            $persona->defaultNarrationStyle(),
            $this->languageProfile,
            $this->answerStyle,
            $this->challengeProfile,
            $this->memoryPolicy,
            $this->interruptionPolicy,
            $traits->thinkingStyle,
            $this->humorLevel,
            $this->curiosity,
            $traits->examples,
            $traits->storytelling,
            $this->debateLevel,
        );
    }

    public function withVoiceProfile(ShadowVoiceProfile $voiceProfile): self
    {
        return new self(
            $this->persona,
            $this->personaTraits,
            $voiceProfile,
            $this->conversationStyle,
            $this->teachingStyle,
            $this->narrationStyle,
            $this->languageProfile,
            $this->answerStyle,
            $this->challengeProfile,
            $this->memoryPolicy,
            $this->interruptionPolicy,
            $this->thinkingStyle,
            $this->humorLevel,
            $this->curiosity,
            $this->examplesLevel,
            $this->storiesLevel,
            $this->debateLevel,
        );
    }

    public function withConversationStyle(ShadowConversationStyle $style): self
    {
        return new self(
            $this->persona,
            $this->personaTraits,
            $this->voiceProfile,
            $style,
            $this->teachingStyle,
            $this->narrationStyle,
            $this->languageProfile,
            $this->answerStyle,
            $this->challengeProfile,
            $this->memoryPolicy,
            $this->interruptionPolicy,
            $this->thinkingStyle,
            $this->humorLevel,
            $this->curiosity,
            $this->examplesLevel,
            $this->storiesLevel,
            $this->debateLevel,
        );
    }

    public function withChallengeProfile(ShadowChallengeProfile $challengeProfile): self
    {
        return new self(
            $this->persona,
            $this->personaTraits,
            $this->voiceProfile,
            $this->conversationStyle,
            $this->teachingStyle,
            $this->narrationStyle,
            $this->languageProfile,
            $this->answerStyle,
            $challengeProfile,
            $this->memoryPolicy,
            $this->interruptionPolicy,
            $this->thinkingStyle,
            $this->humorLevel,
            $this->curiosity,
            $this->examplesLevel,
            $this->storiesLevel,
            $this->debateLevel,
        );
    }

    public function withExamplesLevel(int $examplesLevel): self
    {
        return new self(
            $this->persona,
            $this->personaTraits->withExamples($examplesLevel),
            $this->voiceProfile,
            $this->conversationStyle,
            $this->teachingStyle,
            $this->narrationStyle,
            $this->languageProfile,
            $this->answerStyle,
            $this->challengeProfile,
            $this->memoryPolicy,
            $this->interruptionPolicy,
            $this->thinkingStyle,
            $this->humorLevel,
            $this->curiosity,
            $examplesLevel,
            $this->storiesLevel,
            $this->debateLevel,
        );
    }

    public function withLanguageProfile(ShadowLanguageProfile $languageProfile): self
    {
        return new self(
            $this->persona,
            $this->personaTraits,
            $this->voiceProfile,
            $this->conversationStyle,
            $this->teachingStyle,
            $this->narrationStyle,
            $languageProfile,
            $this->answerStyle,
            $this->challengeProfile,
            $this->memoryPolicy,
            $this->interruptionPolicy,
            $this->thinkingStyle,
            $this->humorLevel,
            $this->curiosity,
            $this->examplesLevel,
            $this->storiesLevel,
            $this->debateLevel,
        );
    }

    public function withMemoryPolicy(ShadowMemoryPolicy $memoryPolicy): self
    {
        return new self(
            $this->persona,
            $this->personaTraits,
            $this->voiceProfile,
            $this->conversationStyle,
            $this->teachingStyle,
            $this->narrationStyle,
            $this->languageProfile,
            $this->answerStyle,
            $this->challengeProfile,
            $memoryPolicy,
            $this->interruptionPolicy,
            $this->thinkingStyle,
            $this->humorLevel,
            $this->curiosity,
            $this->examplesLevel,
            $this->storiesLevel,
            $this->debateLevel,
        );
    }
}
