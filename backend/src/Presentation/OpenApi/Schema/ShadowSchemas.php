<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'WatchContextSegment',
    required: ['index', 'startTime', 'endTime', 'text'],
    properties: [
        new OA\Property(property: 'index', type: 'integer', minimum: 0),
        new OA\Property(property: 'startTime', type: 'number', format: 'float'),
        new OA\Property(property: 'endTime', type: 'number', format: 'float'),
        new OA\Property(property: 'text', type: 'string'),
        new OA\Property(property: 'translatedText', type: 'string', nullable: true),
    ],
)]
final class WatchContextSegmentSchema
{
}

#[OA\Schema(
    schema: 'ShadowInteraction',
    required: ['kind', 'participant', 'videoTimestamp'],
    properties: [
        new OA\Property(property: 'kind', type: 'string', enum: ['question', 'answer', 'pause', 'resume']),
        new OA\Property(property: 'participant', type: 'string', enum: ['user', 'shadow']),
        new OA\Property(property: 'videoTimestamp', type: 'number', format: 'float'),
        new OA\Property(property: 'text', type: 'string', nullable: true),
    ],
)]
final class ShadowInteractionSchema
{
}

#[OA\Schema(
    schema: 'WatchContext',
    required: [
        'videoId',
        'currentTimeSeconds',
        'targetLanguage',
        'nearbyTranscriptContext',
        'nearbyTranslationContext',
        'recentInteractions',
        'conversationMemory',
    ],
    properties: [
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'currentTimeSeconds', type: 'number', format: 'float'),
        new OA\Property(property: 'targetLanguage', type: 'string'),
        new OA\Property(property: 'conversationId', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(
            property: 'currentTranscriptSegment',
            ref: '#/components/schemas/WatchContextSegment',
            nullable: true,
        ),
        new OA\Property(
            property: 'currentTranslationSegment',
            ref: '#/components/schemas/WatchContextSegment',
            nullable: true,
        ),
        new OA\Property(
            property: 'previousTranscriptSegment',
            ref: '#/components/schemas/WatchContextSegment',
            nullable: true,
        ),
        new OA\Property(
            property: 'nextTranscriptSegment',
            ref: '#/components/schemas/WatchContextSegment',
            nullable: true,
        ),
        new OA\Property(
            property: 'previousTranslationSegment',
            ref: '#/components/schemas/WatchContextSegment',
            nullable: true,
        ),
        new OA\Property(
            property: 'nextTranslationSegment',
            ref: '#/components/schemas/WatchContextSegment',
            nullable: true,
        ),
        new OA\Property(property: 'nearbyTranscriptContext', type: 'string'),
        new OA\Property(property: 'nearbyTranslationContext', type: 'string'),
        new OA\Property(property: 'currentSpeaker', type: 'string', nullable: true),
        new OA\Property(
            property: 'recentInteractions',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ShadowInteraction'),
        ),
        new OA\Property(property: 'conversationMemory', type: 'array', items: new OA\Items(type: 'string')),
    ],
)]
final class WatchContextSchema
{
}

#[OA\Schema(
    schema: 'ShadowSession',
    required: [
        'sessionId',
        'videoId',
        'playbackState',
        'targetLanguage',
        'currentTimeSeconds',
        'interactions',
        'policy',
    ],
    properties: [
        new OA\Property(property: 'sessionId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'playbackState', type: 'string', enum: ['playing', 'paused', 'ended']),
        new OA\Property(property: 'targetLanguage', type: 'string'),
        new OA\Property(property: 'currentTimeSeconds', type: 'number', format: 'float'),
        new OA\Property(property: 'currentTranscriptSegmentIndex', type: 'integer', nullable: true),
        new OA\Property(property: 'currentTranslationSegmentIndex', type: 'integer', nullable: true),
        new OA\Property(property: 'contentId', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'conversationId', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(
            property: 'interactions',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ShadowInteraction'),
        ),
        new OA\Property(
            property: 'policy',
            ref: '#/components/schemas/ShadowInterventionPolicy',
        ),
    ],
)]
final class ShadowSessionSchema
{
}

#[OA\Schema(
    schema: 'StartShadowSessionRequest',
    required: ['targetLanguage'],
    properties: [
        new OA\Property(property: 'targetLanguage', type: 'string', example: 'fr'),
        new OA\Property(property: 'contentId', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'conversationId', type: 'string', format: 'uuid', nullable: true),
    ],
)]
final class StartShadowSessionRequestSchema
{
}

#[OA\Schema(
    schema: 'AskShadowQuestionRequest',
    required: ['question', 'time'],
    properties: [
        new OA\Property(property: 'question', type: 'string'),
        new OA\Property(property: 'time', type: 'number', format: 'float'),
    ],
)]
final class AskShadowQuestionRequestSchema
{
}

#[OA\Schema(
    schema: 'ShadowAnswer',
    required: ['sessionId', 'answer', 'currentTimeSeconds', 'session'],
    properties: [
        new OA\Property(property: 'sessionId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'answer', type: 'string'),
        new OA\Property(property: 'currentTimeSeconds', type: 'number', format: 'float'),
        new OA\Property(property: 'currentTranscriptSegmentIndex', type: 'integer', nullable: true),
        new OA\Property(property: 'currentTranslationSegmentIndex', type: 'integer', nullable: true),
        new OA\Property(property: 'session', ref: '#/components/schemas/ShadowSession'),
    ],
)]
final class ShadowAnswerSchema
{
}

#[OA\Schema(
    schema: 'ShadowInterventionPolicy',
    required: [
        'enabled',
        'maxInterventionsPerMinute',
        'minSecondsBetweenInterventions',
        'challengeLevel',
        'explanationStyle',
        'autoResume',
        'allowAutoPause',
    ],
    properties: [
        new OA\Property(property: 'enabled', type: 'boolean'),
        new OA\Property(property: 'maxInterventionsPerMinute', type: 'integer', minimum: 0),
        new OA\Property(property: 'minSecondsBetweenInterventions', type: 'number', format: 'float'),
        new OA\Property(property: 'challengeLevel', type: 'string', enum: ['easy', 'normal', 'hard']),
        new OA\Property(
            property: 'explanationStyle',
            type: 'string',
            enum: ['short', 'detailed', 'example_first'],
        ),
        new OA\Property(property: 'autoResume', type: 'boolean'),
        new OA\Property(property: 'allowAutoPause', type: 'boolean'),
    ],
)]
final class ShadowInterventionPolicySchema
{
}

#[OA\Schema(
    schema: 'ShadowChallenge',
    required: ['questionText'],
    properties: [
        new OA\Property(property: 'questionText', type: 'string'),
        new OA\Property(property: 'suggestedAnswer', type: 'string', nullable: true),
    ],
)]
final class ShadowChallengeSchema
{
}

#[OA\Schema(
    schema: 'ShadowIntervention',
    required: [
        'id',
        'type',
        'trigger',
        'reason',
        'videoTimestamp',
        'expectedUserAction',
        'allowAutoPause',
        'skipped',
        'answered',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(
            property: 'type',
            type: 'string',
            enum: ['vocabulary_check', 'concept_check', 'explanation', 'recap'],
        ),
        new OA\Property(
            property: 'trigger',
            type: 'string',
            enum: [
                'unknown_vocabulary',
                'complex_sentence',
                'long_segment',
                'segment_boundary',
                'idle_watch',
            ],
        ),
        new OA\Property(property: 'reason', type: 'string'),
        new OA\Property(property: 'videoTimestamp', type: 'number', format: 'float'),
        new OA\Property(property: 'expectedUserAction', type: 'string'),
        new OA\Property(property: 'allowAutoPause', type: 'boolean'),
        new OA\Property(property: 'explanation', type: 'string', nullable: true),
        new OA\Property(
            property: 'challenge',
            ref: '#/components/schemas/ShadowChallenge',
            nullable: true,
        ),
        new OA\Property(property: 'skipped', type: 'boolean'),
        new OA\Property(property: 'answered', type: 'boolean'),
    ],
)]
final class ShadowInterventionSchema
{
}

#[OA\Schema(
    schema: 'ShadowInterventionCheck',
    required: ['hasIntervention', 'recommendPause', 'recommendResume', 'session'],
    properties: [
        new OA\Property(property: 'hasIntervention', type: 'boolean'),
        new OA\Property(
            property: 'intervention',
            ref: '#/components/schemas/ShadowIntervention',
            nullable: true,
        ),
        new OA\Property(property: 'recommendPause', type: 'boolean'),
        new OA\Property(property: 'recommendResume', type: 'boolean'),
        new OA\Property(property: 'session', ref: '#/components/schemas/ShadowSession'),
    ],
)]
final class ShadowInterventionCheckSchema
{
}

#[OA\Schema(
    schema: 'UpdateShadowInterventionPolicyRequest',
    properties: [
        new OA\Property(property: 'enabled', type: 'boolean', nullable: true),
        new OA\Property(property: 'maxInterventionsPerMinute', type: 'integer', nullable: true),
        new OA\Property(
            property: 'minSecondsBetweenInterventions',
            type: 'number',
            format: 'float',
            nullable: true,
        ),
        new OA\Property(
            property: 'challengeLevel',
            type: 'string',
            enum: ['easy', 'normal', 'hard'],
            nullable: true,
        ),
        new OA\Property(
            property: 'explanationStyle',
            type: 'string',
            enum: ['short', 'detailed', 'example_first'],
            nullable: true,
        ),
        new OA\Property(property: 'autoResume', type: 'boolean', nullable: true),
        new OA\Property(property: 'allowAutoPause', type: 'boolean', nullable: true),
    ],
)]
final class UpdateShadowInterventionPolicyRequestSchema
{
}

#[OA\Schema(
    schema: 'ShadowInterventionPolicyResponse',
    required: ['policy'],
    properties: [
        new OA\Property(
            property: 'policy',
            ref: '#/components/schemas/ShadowInterventionPolicy',
        ),
    ],
)]
final class ShadowInterventionPolicyResponseSchema
{
}

#[OA\Schema(
    schema: 'AnswerShadowInterventionRequest',
    required: ['answer', 'time'],
    properties: [
        new OA\Property(property: 'answer', type: 'string'),
        new OA\Property(property: 'time', type: 'number', format: 'float'),
    ],
)]
final class AnswerShadowInterventionRequestSchema
{
}

#[OA\Schema(
    schema: 'ShadowInterventionAnswer',
    required: ['sessionId', 'interventionId', 'reply', 'recommendResume', 'session'],
    properties: [
        new OA\Property(property: 'sessionId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'interventionId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'reply', type: 'string'),
        new OA\Property(property: 'recommendResume', type: 'boolean'),
        new OA\Property(property: 'session', ref: '#/components/schemas/ShadowSession'),
    ],
)]
final class ShadowInterventionAnswerSchema
{
}
