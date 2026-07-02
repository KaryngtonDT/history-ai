<?php

declare(strict_types=1);

namespace App\Application\ShadowConfiguration;

final class ShadowConfigurationIntentDetector
{
    /**
     * @var list<array{pattern: string, intent: ShadowConfigurationIntent, parameters?: array<string, mixed>, explanation: string, confidence: float}>
     */
    private const RULES = [
        [
            'pattern' => '/(?:parle|speak|sprich).*(?:moins vite|slower|langsamer)/iu',
            'intent' => ShadowConfigurationIntent::ChangeSpeed,
            'parameters' => ['direction' => 'decrease'],
            'explanation' => 'Detected request to speak slower.',
            'confidence' => 0.92,
        ],
        [
            'pattern' => '/(?:parle|speak|sprich).*(?:plus vite|faster|schneller)/iu',
            'intent' => ShadowConfigurationIntent::ChangeSpeed,
            'parameters' => ['direction' => 'increase'],
            'explanation' => 'Detected request to speak faster.',
            'confidence' => 0.92,
        ],
        [
            'pattern' => '/(?:voix de conteur|storyteller voice|stimme.*(?:erzähler|storyteller))/iu',
            'intent' => ShadowConfigurationIntent::ChangePersona,
            'parameters' => ['persona' => 'storyteller'],
            'explanation' => 'Detected request for storyteller persona.',
            'confidence' => 0.95,
        ],
        [
            'pattern' => '/(?:mode professeur|professor mode|professor modus)/iu',
            'intent' => ShadowConfigurationIntent::ChangePersona,
            'parameters' => ['persona' => 'professor'],
            'explanation' => 'Detected request for professor persona.',
            'confidence' => 0.93,
        ],
        [
            'pattern' => '/(?:challenge[- ]moi|challenge me|fordere mich).*(?:plus|more|mehr|davantage)/iu',
            'intent' => ShadowConfigurationIntent::ChangeChallenge,
            'parameters' => ['direction' => 'increase'],
            'explanation' => 'Detected request to increase challenge level.',
            'confidence' => 0.9,
        ],
        [
            'pattern' => '/(?:challenge[- ]moi|challenge me|fordere mich).*(?:moins|less|weniger)/iu',
            'intent' => ShadowConfigurationIntent::ChangeChallenge,
            'parameters' => ['direction' => 'decrease'],
            'explanation' => 'Detected request to decrease challenge level.',
            'confidence' => 0.9,
        ],
        [
            'pattern' => '/(?:sois plus naturel|be more natural|sei natürlicher)/iu',
            'intent' => ShadowConfigurationIntent::UpdateConversationStyle,
            'parameters' => ['style' => 'friendly'],
            'explanation' => 'Detected request for a more natural conversation style.',
            'confidence' => 0.88,
        ],
        [
            'pattern' => '/(?:explique.*exemples|always.*examples|immer.*beispiele)/iu',
            'intent' => ShadowConfigurationIntent::UpdateLearningStyle,
            'parameters' => ['examples' => 'very_high'],
            'explanation' => 'Detected request to explain with more examples.',
            'confidence' => 0.9,
        ],
        [
            'pattern' => '/(?:oublie cette préférence|forget this preference|vergiss diese präferenz)/iu',
            'intent' => ShadowConfigurationIntent::ForgetPreference,
            'parameters' => [],
            'explanation' => 'Detected request to forget a specific preference.',
            'confidence' => 0.85,
        ],
        [
            'pattern' => '/(?:réinitialise.*profil|reset.*profile|profil zurücksetzen)/iu',
            'intent' => ShadowConfigurationIntent::ResetProfile,
            'parameters' => [],
            'explanation' => 'Detected request to reset the full Shadow profile.',
            'confidence' => 0.96,
        ],
        [
            'pattern' => '/(?:parle uniquement en allemand|speak only german|nur deutsch)/iu',
            'intent' => ShadowConfigurationIntent::ChangeLanguage,
            'parameters' => ['primaryLanguage' => 'de'],
            'explanation' => 'Detected request to use German as primary language.',
            'confidence' => 0.91,
        ],
        [
            'pattern' => '/(?:explique en français|explain in french|auf französisch erklären)/iu',
            'intent' => ShadowConfigurationIntent::ChangeLanguage,
            'parameters' => ['primaryLanguage' => 'fr'],
            'explanation' => 'Detected request to explain in French.',
            'confidence' => 0.91,
        ],
        [
            'pattern' => '/(?:termes techniques.*anglais|technical terms.*english|fachbegriffe.*englisch)/iu',
            'intent' => ShadowConfigurationIntent::ChangeTechnicalTerms,
            'parameters' => ['policy' => 'always_original', 'technicalLanguage' => 'en'],
            'explanation' => 'Detected request to keep technical terms in English.',
            'confidence' => 0.9,
        ],
        [
            'pattern' => '/(?:réponses plus courtes|shorter answers|kürzere antworten)/iu',
            'intent' => ShadowConfigurationIntent::ChangeAnswerLength,
            'parameters' => ['answerStyle' => 'short'],
            'explanation' => 'Detected request for shorter answers.',
            'confidence' => 0.88,
        ],
        [
            'pattern' => '/(?:plus d\'humour|more humor|mehr humor)/iu',
            'intent' => ShadowConfigurationIntent::ChangeHumor,
            'parameters' => ['humor' => 'high'],
            'explanation' => 'Detected request for more humor.',
            'confidence' => 0.87,
        ],
    ];

    public function detect(string $utterance): ShadowConfigurationDetection
    {
        $normalized = trim($utterance);

        if ('' === $normalized) {
            return new ShadowConfigurationDetection(
                ShadowConfigurationIntent::Unknown,
                [],
                'Empty utterance.',
                0.0,
            );
        }

        foreach (self::RULES as $rule) {
            if (1 === preg_match($rule['pattern'], $normalized)) {
                return new ShadowConfigurationDetection(
                    $rule['intent'],
                    $rule['parameters'] ?? [],
                    $rule['explanation'],
                    $rule['confidence'],
                );
            }
        }

        return new ShadowConfigurationDetection(
            ShadowConfigurationIntent::Unknown,
            [],
            'No deterministic configuration intent matched.',
            0.0,
        );
    }
}
