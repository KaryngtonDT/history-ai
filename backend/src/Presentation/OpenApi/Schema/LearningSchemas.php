<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LearningSignal',
    required: ['id', 'type', 'recordedAt', 'context'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'recordedAt', type: 'string', format: 'date-time'),
        new OA\Property(property: 'context', type: 'object'),
    ],
)]
final class LearningSignalSchema
{
}

#[OA\Schema(
    schema: 'LearningInsight',
    required: ['id', 'type', 'summary', 'sourceSignalIds', 'generatedAt'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'summary', type: 'string'),
        new OA\Property(
            property: 'sourceSignalIds',
            type: 'array',
            items: new OA\Items(type: 'string', format: 'uuid'),
        ),
        new OA\Property(property: 'generatedAt', type: 'string', format: 'date-time'),
    ],
)]
final class LearningInsightSchema
{
}

#[OA\Schema(
    schema: 'LearningRecommendation',
    required: ['id', 'type', 'explanation', 'sourceInsightIds', 'generatedAt'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'explanation', type: 'string'),
        new OA\Property(
            property: 'sourceInsightIds',
            type: 'array',
            items: new OA\Items(type: 'string', format: 'uuid'),
        ),
        new OA\Property(property: 'generatedAt', type: 'string', format: 'date-time'),
    ],
)]
final class LearningRecommendationSchema
{
}

#[OA\Schema(
    schema: 'LearningPreference',
    required: ['key', 'enabled'],
    properties: [
        new OA\Property(property: 'key', type: 'string'),
        new OA\Property(property: 'enabled', type: 'boolean'),
    ],
)]
final class LearningPreferenceSchema
{
}

#[OA\Schema(
    schema: 'LearningProfile',
    required: [
        'id',
        'scopeKey',
        'adaptiveRecommendationsEnabled',
        'preferences',
        'signals',
        'insights',
        'recommendations',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'scopeKey', type: 'string'),
        new OA\Property(property: 'adaptiveRecommendationsEnabled', type: 'boolean'),
        new OA\Property(
            property: 'preferences',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/LearningPreference'),
        ),
        new OA\Property(
            property: 'signals',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/LearningSignal'),
        ),
        new OA\Property(
            property: 'insights',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/LearningInsight'),
        ),
        new OA\Property(
            property: 'recommendations',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/LearningRecommendation'),
        ),
    ],
)]
final class LearningProfileSchema
{
}

#[OA\Schema(
    schema: 'UpdateLearningPreferencesRequest',
    properties: [
        new OA\Property(property: 'scopeKey', type: 'string'),
        new OA\Property(property: 'adaptiveRecommendationsEnabled', type: 'boolean'),
    ],
)]
final class UpdateLearningPreferencesRequestSchema
{
}

#[OA\Schema(
    schema: 'RecordLearningSignalsRequest',
    required: ['source'],
    properties: [
        new OA\Property(property: 'scopeKey', type: 'string'),
        new OA\Property(property: 'source', type: 'string', enum: ['shadow', 'review', 'telemetry', 'quality']),
        new OA\Property(property: 'event', type: 'string'),
    ],
    additionalProperties: true,
)]
final class RecordLearningSignalsRequestSchema
{
}
