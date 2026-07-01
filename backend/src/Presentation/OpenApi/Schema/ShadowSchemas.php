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
