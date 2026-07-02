<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship;

use App\Domain\ShadowRelationship\RelationshipPendingChange;
use App\Domain\ShadowRelationship\RelationshipProfile;
use App\Domain\ShadowRelationship\RelationshipRepositoryInterface;
use App\Domain\ShadowRelationship\RelationshipStrength;
use App\Domain\ShadowRelationship\RelationshipTrait;
use App\Domain\ShadowRelationship\RelationshipTraitType;

final class RelationshipUtteranceInterpreter
{
    public function __construct(
        private readonly RelationshipRepositoryInterface $repository,
        private readonly RelationshipEvolutionEngine $evolutionEngine,
        private readonly RelationshipJsonMapper $mapper,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function interpret(string $utterance, string $scopeKey = 'default', bool $confirmed = false): array
    {
        $profile = $this->resolveProfile($scopeKey);
        $detection = $this->detect($utterance);
        $requiresConfirmation = $detection['requiresConfirmation'];

        if ($detection['trait'] instanceof RelationshipTrait && ($confirmed || !$requiresConfirmation)) {
            if ($requiresConfirmation && $confirmed) {
                $profile = $this->evolutionEngine->applyTrait($profile, $detection['trait']->confirm());
            } else {
                $profile = $this->evolutionEngine->applyTrait($profile, $detection['trait']);
            }
            $this->repository->save($profile);
        } elseif ($detection['trait'] instanceof RelationshipTrait && $requiresConfirmation) {
            $profile = $profile->proposeChange(
                RelationshipPendingChange::propose($detection['trait'], $detection['previewLabel']),
            );
            $this->repository->save($profile);
        }

        return [
            'intent' => $detection['intent'],
            'previewLabel' => $detection['previewLabel'],
            'requiresConfirmation' => $requiresConfirmation && !$confirmed,
            'confirmationMessage' => $detection['confirmationMessage'],
            'applied' => $detection['trait'] instanceof RelationshipTrait && ($confirmed || !$requiresConfirmation),
            'profile' => $this->mapper->toArray($profile),
            'portrait' => (new RelationshipPortraitBuilder())->build($profile),
        ];
    }

  /**
     * @return array{intent: string, previewLabel: string, confirmationMessage: string, requiresConfirmation: bool, trait: ?RelationshipTrait}
     */
    private function detect(string $utterance): array
    {
        $lower = strtolower($utterance);

        if (str_contains($lower, 'football') && (str_contains($lower, 'analog') || str_contains($lower, 'remember'))) {
            $trait = RelationshipTrait::explicit(
                RelationshipTraitType::Habit,
                'football_analogies',
                'Explain with football analogies',
                RelationshipStrength::VeryHigh,
                'User explicitly asked to use football analogies.',
            );

            return $this->result('remember_habit', $trait, 'Apply football analogies in explanations?');
        }

        if (str_contains($lower, 'shorter') || str_contains($lower, 'plus court') || str_contains($lower, 'kürzer')) {
            $trait = RelationshipTrait::explicit(
                RelationshipTraitType::Habit,
                'short_answers',
                'Prefers shorter answers',
                RelationshipStrength::High,
                'User asked for shorter answers.',
            );

            return $this->result('preference_short', $trait, 'Prefer shorter answers?');
        }

        if (str_contains($lower, 'docker') && (str_contains($lower, 'comfortable') || str_contains($lower, 'à l\'aise'))) {
            $trait = RelationshipTrait::explicit(
                RelationshipTraitType::Interest,
                'docker',
                'Comfortable with Docker',
                RelationshipStrength::VeryHigh,
                'User stated comfort with Docker.',
            );

            return $this->result('known_skill', $trait, 'Mark Docker as a known topic?', false);
        }

        if (str_contains($lower, 'no more') && str_contains($lower, 'economic')) {
            $trait = RelationshipTrait::explicit(
                RelationshipTraitType::Habit,
                'avoid_economic_metaphors',
                'Avoid economic metaphors',
                RelationshipStrength::VeryHigh,
                'User asked to stop using economic metaphors.',
            );

            return $this->result('avoid_metaphor', $trait, 'Stop using economic metaphors?');
        }

        return [
            'intent' => 'unknown',
            'previewLabel' => '',
            'confirmationMessage' => 'I could not map that request to a relationship trait yet.',
            'requiresConfirmation' => false,
            'trait' => null,
        ];
    }

    /**
     * @return array{intent: string, previewLabel: string, confirmationMessage: string, requiresConfirmation: bool, trait: RelationshipTrait}
     */
    private function result(string $intent, RelationshipTrait $trait, string $message, bool $requiresConfirmation = true): array
    {
        return [
            'intent' => $intent,
            'previewLabel' => $trait->label(),
            'confirmationMessage' => $message,
            'requiresConfirmation' => $requiresConfirmation,
            'trait' => $trait,
        ];
    }

    private function resolveProfile(string $scopeKey): RelationshipProfile
    {
        $existing = $this->repository->findByScope($scopeKey);

        if (null !== $existing) {
            return $existing;
        }

        $profile = RelationshipProfile::create(scopeKey: $scopeKey);
        $this->repository->save($profile);

        return $profile;
    }
}
