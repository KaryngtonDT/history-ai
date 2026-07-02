<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship;

use App\Domain\ShadowRelationship\RelationshipStrength;
use App\Domain\ShadowRelationship\RelationshipTrait;
use App\Domain\ShadowRelationship\RelationshipTraitType;

final class InterestDetector
{
    /** @var array<string, string> */
    private const TOPIC_KEYWORDS = [
        'programming' => 'programming|software|code|kubernetes|docker|symfony|api|developer',
        'philosophy' => 'philosophy|nietzsche|ethics|metaphysics|socratic',
        'football' => 'football|soccer|match|stadium|goal',
        'economics' => 'economics|market|inflation|gdp|finance',
        'english' => 'english|vocabulary|grammar|pronunciation',
        'languages' => 'language|translation|bilingual|french|german',
    ];

    /**
     * @param array<string, mixed> $payload
     *
     * @return list<RelationshipTrait>
     */
    public function detect(array $payload): array
    {
        $text = strtolower($this->flattenText($payload));
        $traits = [];

        foreach (self::TOPIC_KEYWORDS as $key => $pattern) {
            if (1 === preg_match('/\b('.$pattern.')\b/i', $text)) {
                $traits[] = RelationshipTrait::inferred(
                    RelationshipTraitType::Interest,
                    $key,
                    ucfirst(str_replace('_', ' ', $key)),
                    RelationshipStrength::Medium,
                    sprintf('Detected interest signal for %s.', $key),
                );
            }
        }

        return $traits;
    }

    /** @param array<string, mixed> $payload */
    private function flattenText(array $payload): string
    {
        $parts = [];

        foreach ($payload as $value) {
            if (is_string($value)) {
                $parts[] = $value;
            } elseif (is_array($value)) {
                $parts[] = $this->flattenText($value);
            }
        }

        return implode(' ', $parts);
    }
}
