<?php

declare(strict_types=1);

namespace App\Infrastructure\Shadow;

use App\Domain\Chat\ConversationId;
use App\Domain\Content\ContentId;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowAnswer;
use App\Domain\Shadow\ShadowChallenge;
use App\Domain\Shadow\ShadowChallengeLevel;
use App\Domain\Shadow\ShadowExplanationStyle;
use App\Domain\Shadow\ShadowInteraction;
use App\Domain\Shadow\ShadowInteractionCollection;
use App\Domain\Shadow\ShadowInteractionKind;
use App\Domain\Shadow\ShadowIntervention;
use App\Domain\Shadow\ShadowInterventionCollection;
use App\Domain\Shadow\ShadowInterventionId;
use App\Domain\Shadow\ShadowInterventionPolicy;
use App\Domain\Shadow\ShadowInterventionTrigger;
use App\Domain\Shadow\ShadowInterventionType;
use App\Domain\Shadow\ShadowParticipant;
use App\Domain\Shadow\ShadowPlaybackState;
use App\Domain\Shadow\ShadowQuestion;
use App\Domain\Shadow\ShadowSession;
use App\Domain\Shadow\ShadowSessionId;
use App\Domain\Shadow\ShadowTimestamp;
use App\Domain\Shadow\ShadowVoiceLanguage;
use App\Domain\Shadow\ShadowVoiceMode;
use App\Domain\Shadow\ShadowVoicePreference;
use App\Domain\Video\VideoId;
use JsonException;

final class ShadowSessionPersistenceMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(ShadowSession $session): array
    {
        return [
            'id' => $session->id()->value,
            'videoId' => $session->videoId()->value,
            'contentId' => $session->contentId()?->value,
            'conversationId' => $session->conversationId()?->value,
            'currentTimeSeconds' => $session->currentTimestamp()->seconds(),
            'playbackState' => $session->playbackState()->value,
            'targetLanguage' => $session->targetLanguage(),
            'currentTranscriptSegmentIndex' => $session->currentTranscriptSegmentIndex(),
            'currentTranslationSegmentIndex' => $session->currentTranslationSegmentIndex(),
            'interactions' => array_map(
                fn (ShadowInteraction $interaction): array => $this->interactionToArray($interaction),
                $session->interactions()->all(),
            ),
            'policy' => $this->policyToArray($session->interventionPolicy()),
            'voicePreference' => $this->voicePreferenceToArray($session->voicePreference()),
            'interventions' => array_map(
                fn (ShadowIntervention $intervention): array => $this->interventionToArray($intervention),
                $session->interventions()->all(),
            ),
        ];
    }

    public function toJson(ShadowSession $session): string
    {
        return json_encode($this->toArray($session), JSON_THROW_ON_ERROR);
    }

    public function fromJson(string $json): ShadowSession
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidShadowSessionException('Stored shadow session is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new InvalidShadowSessionException('Stored shadow session must be a JSON object.');
        }

        $id = is_string($decoded['id'] ?? null) ? $decoded['id'] : null;
        $videoId = is_string($decoded['videoId'] ?? null) ? $decoded['videoId'] : null;
        $targetLanguage = is_string($decoded['targetLanguage'] ?? null) ? $decoded['targetLanguage'] : 'en';

        if (null === $id || null === $videoId) {
            throw new InvalidShadowSessionException('Stored shadow session is missing id or videoId.');
        }

        $playbackState = ShadowPlaybackState::tryFrom((string) ($decoded['playbackState'] ?? ''))
            ?? ShadowPlaybackState::Playing;

        return new ShadowSession(
            new ShadowSessionId($id),
            new VideoId($videoId),
            is_string($decoded['contentId'] ?? null) ? new ContentId($decoded['contentId']) : null,
            is_string($decoded['conversationId'] ?? null) ? new ConversationId($decoded['conversationId']) : null,
            ShadowTimestamp::fromSeconds((float) ($decoded['currentTimeSeconds'] ?? 0.0)),
            $playbackState,
            $targetLanguage,
            is_int($decoded['currentTranscriptSegmentIndex'] ?? null)
                ? $decoded['currentTranscriptSegmentIndex']
                : null,
            is_int($decoded['currentTranslationSegmentIndex'] ?? null)
                ? $decoded['currentTranslationSegmentIndex']
                : null,
            $this->interactionsFromArray(is_array($decoded['interactions'] ?? null) ? $decoded['interactions'] : []),
            $this->policyFromArray(is_array($decoded['policy'] ?? null) ? $decoded['policy'] : []),
            $this->voicePreferenceFromArray(
                is_array($decoded['voicePreference'] ?? null) ? $decoded['voicePreference'] : [],
            ),
            $this->interventionsFromArray(
                is_array($decoded['interventions'] ?? null) ? $decoded['interventions'] : [],
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function interactionToArray(ShadowInteraction $interaction): array
    {
        $data = [
            'kind' => $interaction->kind()->value,
            'participant' => $interaction->participant()->value,
            'videoTimestamp' => $interaction->videoTimestamp()->seconds(),
            'recordedAt' => $interaction->recordedAt()->format(DATE_ATOM),
        ];

        if (null !== $interaction->question()) {
            $data['question'] = $interaction->question()->text();
        }

        if (null !== $interaction->answer()) {
            $data['answer'] = $interaction->answer()->text();
        }

        return $data;
    }

    /**
     * @param list<array<string, mixed>> $data
     */
    private function interactionsFromArray(array $data): ShadowInteractionCollection
    {
        $collection = ShadowInteractionCollection::empty();

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            $kind = ShadowInteractionKind::tryFrom((string) ($item['kind'] ?? ''));
            $participant = ShadowParticipant::tryFrom((string) ($item['participant'] ?? ''));
            $timestamp = ShadowTimestamp::fromSeconds((float) ($item['videoTimestamp'] ?? 0.0));
            $recordedAtValue = is_string($item['recordedAt'] ?? null) ? $item['recordedAt'] : null;
            $recordedAt = $recordedAtValue
                ? \DateTimeImmutable::createFromFormat(DATE_ATOM, $recordedAtValue) ?: new \DateTimeImmutable()
                : new \DateTimeImmutable();

            if (null === $kind || null === $participant) {
                continue;
            }

            $interaction = match ($kind) {
                ShadowInteractionKind::Question => is_string($item['question'] ?? null)
                    ? ShadowInteraction::createQuestion(
                        ShadowQuestion::fromString($item['question']),
                        $timestamp,
                        $recordedAt,
                    )
                    : null,
                ShadowInteractionKind::Answer => is_string($item['answer'] ?? null)
                    ? ShadowInteraction::createAnswer(
                        ShadowAnswer::fromString($item['answer']),
                        $timestamp,
                        $recordedAt,
                    )
                    : null,
                ShadowInteractionKind::Pause => ShadowInteraction::createPause($timestamp, $recordedAt),
                ShadowInteractionKind::Resume => ShadowInteraction::createResume($timestamp, $recordedAt),
            };

            if (null === $interaction) {
                continue;
            }

            $collection = $collection->append($interaction);
        }

        return $collection;
    }

    /**
     * @return array<string, mixed>
     */
    private function policyToArray(ShadowInterventionPolicy $policy): array
    {
        return [
            'enabled' => $policy->enabled(),
            'maxInterventionsPerMinute' => $policy->maxInterventionsPerMinute(),
            'minSecondsBetweenInterventions' => $policy->minSecondsBetweenInterventions(),
            'challengeLevel' => $policy->challengeLevel()->value,
            'explanationStyle' => $policy->explanationStyle()->value,
            'autoResume' => $policy->autoResume(),
            'allowAutoPause' => $policy->allowAutoPause(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function policyFromArray(array $data): ShadowInterventionPolicy
    {
        if ([] === $data) {
            return ShadowInterventionPolicy::disabled();
        }

        return new ShadowInterventionPolicy(
            enabled: (bool) ($data['enabled'] ?? false),
            maxInterventionsPerMinute: (int) ($data['maxInterventionsPerMinute'] ?? 1),
            minSecondsBetweenInterventions: (float) ($data['minSecondsBetweenInterventions'] ?? 60.0),
            challengeLevel: ShadowChallengeLevel::tryFrom((string) ($data['challengeLevel'] ?? ''))
                ?? ShadowChallengeLevel::Easy,
            explanationStyle: ShadowExplanationStyle::tryFrom((string) ($data['explanationStyle'] ?? ''))
                ?? ShadowExplanationStyle::Short,
            autoResume: (bool) ($data['autoResume'] ?? false),
            allowAutoPause: (bool) ($data['allowAutoPause'] ?? false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function voicePreferenceToArray(ShadowVoicePreference $preference): array
    {
        return [
            'mode' => $preference->mode()->value,
            'manualLanguage' => $preference->manualLanguage()?->value,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function voicePreferenceFromArray(array $data): ShadowVoicePreference
    {
        if ([] === $data) {
            return ShadowVoicePreference::default();
        }

        $mode = ShadowVoiceMode::tryFrom((string) ($data['mode'] ?? '')) ?? ShadowVoiceMode::SameAsTargetLanguage;
        $manualLanguage = is_string($data['manualLanguage'] ?? null)
            ? ShadowVoiceLanguage::tryFrom($data['manualLanguage'])
            : null;

        return new ShadowVoicePreference($mode, $manualLanguage);
    }

    /**
     * @return array<string, mixed>
     */
    private function interventionToArray(ShadowIntervention $intervention): array
    {
        $data = [
            'id' => $intervention->id()->value,
            'type' => $intervention->type()->value,
            'trigger' => $intervention->trigger()->value,
            'reason' => $intervention->reason(),
            'videoTimestamp' => $intervention->videoTimestamp()->seconds(),
            'expectedUserAction' => $intervention->expectedUserAction(),
            'allowAutoPause' => $intervention->allowAutoPause(),
            'skipped' => $intervention->isSkipped(),
            'answered' => $intervention->isAnswered(),
            'explanation' => $intervention->explanation(),
            'suggestedAnswer' => $intervention->suggestedAnswer(),
        ];

        if (null !== $intervention->challenge()) {
            $data['challenge'] = [
                'questionText' => $intervention->challenge()->questionText(),
                'suggestedAnswer' => $intervention->challenge()->suggestedAnswer(),
            ];
        }

        return $data;
    }

    /**
     * @param list<array<string, mixed>> $data
     */
    private function interventionsFromArray(array $data): ShadowInterventionCollection
    {
        $collection = ShadowInterventionCollection::empty();

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            $id = is_string($item['id'] ?? null) ? $item['id'] : null;
            $type = ShadowInterventionType::tryFrom((string) ($item['type'] ?? ''));
            $trigger = ShadowInterventionTrigger::tryFrom((string) ($item['trigger'] ?? ''));
            $reason = is_string($item['reason'] ?? null) ? $item['reason'] : null;
            $expectedUserAction = is_string($item['expectedUserAction'] ?? null) ? $item['expectedUserAction'] : null;

            if (null === $id || null === $type || null === $trigger || null === $reason || null === $expectedUserAction) {
                continue;
            }

            $challenge = null;

            if (is_array($item['challenge'] ?? null)) {
                $challengeData = $item['challenge'];
                $questionText = is_string($challengeData['questionText'] ?? null) ? $challengeData['questionText'] : null;

                if (null !== $questionText) {
                    $challenge = ShadowChallenge::create(
                        $questionText,
                        is_string($challengeData['suggestedAnswer'] ?? null) ? $challengeData['suggestedAnswer'] : null,
                    );
                }
            }

            $intervention = new ShadowIntervention(
                new ShadowInterventionId($id),
                $type,
                $trigger,
                $reason,
                ShadowTimestamp::fromSeconds((float) ($item['videoTimestamp'] ?? 0.0)),
                $expectedUserAction,
                (bool) ($item['allowAutoPause'] ?? false),
                $challenge,
                is_string($item['explanation'] ?? null) ? $item['explanation'] : null,
                is_string($item['suggestedAnswer'] ?? null) ? $item['suggestedAnswer'] : null,
                (bool) ($item['skipped'] ?? false),
                (bool) ($item['answered'] ?? false),
            );

            $collection = $collection->append($intervention);
        }

        return $collection;
    }
}
