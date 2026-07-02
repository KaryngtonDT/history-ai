<?php

declare(strict_types=1);

namespace App\Application\ShadowVoice;

final class ShadowVoiceCatalog
{
    /**
     * @return list<ShadowVoiceDefinition>
     */
    public static function all(): array
    {
        return [
            self::voice('storyteller-warm-en', 'Warm Storyteller', ShadowVoiceCollection::GreatStorytellers, 'en', 'female', 'American', 'high', 'low', 'Let me tell you a story about how ideas travel across centuries.'),
            self::voice('storyteller-deep-en', 'Deep Narrator', ShadowVoiceCollection::GreatStorytellers, 'en', 'male', 'British', 'high', 'low', 'Once upon a time, knowledge was passed from voice to voice.'),
            self::voice('storyteller-fr', 'Conteur Français', ShadowVoiceCollection::GreatStorytellers, 'fr', 'male', 'French', 'high', 'low', 'Laissez-moi vous raconter une histoire fascinante.'),
            self::voice('documentary-calm-en', 'Calm Documentary', ShadowVoiceCollection::DocumentaryNarrators, 'en', 'male', 'British', 'high', 'medium', 'In the archives of history, every detail matters.'),
            self::voice('documentary-neutral-en', 'Neutral Documentary', ShadowVoiceCollection::DocumentaryNarrators, 'en', 'female', 'American', 'high', 'medium', 'This segment explores how civilizations shaped modern thought.'),
            self::voice('professor-clear-en', 'Clear Professor', ShadowVoiceCollection::UniversityProfessors, 'en', 'male', 'American', 'high', 'low', 'Let us begin with the principle, then examine a concrete example.'),
            self::voice('professor-academic-fr', 'Professeur Académique', ShadowVoiceCollection::UniversityProfessors, 'fr', 'female', 'French', 'high', 'low', 'Commençons par définir le concept, puis passons à l\'application.'),
            self::voice('technical-precise-en', 'Precise Technical', ShadowVoiceCollection::TechnicalExperts, 'en', 'male', 'American', 'high', 'low', 'The API endpoint returns a deterministic configuration payload.'),
            self::voice('technical-analytical-de', 'Analytisch Technisch', ShadowVoiceCollection::TechnicalExperts, 'de', 'female', 'German', 'high', 'medium', 'Dieses Modul verarbeitet die Pipeline-Ergebnisse deterministisch.'),
            self::voice('friendly-warm-en', 'Warm Companion', ShadowVoiceCollection::FriendlyCompanions, 'en', 'female', 'American', 'medium', 'low', 'Hey! Let us explore this together at your pace.'),
            self::voice('friendly-casual-fr', 'Compagnon Amical', ShadowVoiceCollection::FriendlyCompanions, 'fr', 'female', 'French', 'medium', 'low', 'Salut ! On regarde ça ensemble, pas à pas.'),
            self::voice('business-confident-en', 'Confident Speaker', ShadowVoiceCollection::BusinessSpeakers, 'en', 'male', 'American', 'high', 'low', 'The strategic takeaway is clarity, focus, and measurable outcomes.'),
            self::voice('debate-energetic-en', 'Energetic Debater', ShadowVoiceCollection::DebateMasters, 'en', 'male', 'British', 'high', 'medium', 'On one hand we see the argument; on the other, a compelling counterpoint.'),
            self::voice('socratic-reflective-en', 'Reflective Mentor', ShadowVoiceCollection::SocraticMentors, 'en', 'female', 'American', 'high', 'low', 'What do you think would happen if we changed this assumption?'),
            self::voice('browser-default', 'Browser Default', ShadowVoiceCollection::FriendlyCompanions, 'en', 'neutral', 'System', 'medium', 'low', 'Hello, I am Shadow. This is a voice preview.'),
        ];
    }

    public static function findById(string $voiceId): ?ShadowVoiceDefinition
    {
        foreach (self::all() as $voice) {
            if ($voice->id() === $voiceId) {
                return $voice;
            }
        }

        return null;
    }

    /**
     * @return list<ShadowVoiceDefinition>
     */
    public static function forCollection(ShadowVoiceCollection $collection): array
    {
        return array_values(array_filter(
            self::all(),
            static fn (ShadowVoiceDefinition $voice): bool => $voice->collection() === $collection,
        ));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function engines(): array
    {
        return array_map(
            static fn (ShadowVoiceEngine $engine): array => [
                'id' => $engine->value,
                'label' => $engine->label(),
                'available' => $engine->isAvailable(),
            ],
            ShadowVoiceEngine::cases(),
        );
    }

    private static function voice(
        string $id,
        string $name,
        ShadowVoiceCollection $collection,
        string $language,
        string $gender,
        string $accent,
        string $quality,
        string $latency,
        string $previewText,
    ): ShadowVoiceDefinition {
        return new ShadowVoiceDefinition(
            $id,
            $name,
            ShadowVoiceEngine::BrowserTts,
            [$language],
            $gender,
            $accent,
            $quality,
            $latency,
            $previewText,
            $collection,
        );
    }
}
