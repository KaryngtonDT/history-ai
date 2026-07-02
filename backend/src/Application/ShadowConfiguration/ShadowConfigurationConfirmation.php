<?php

declare(strict_types=1);

namespace App\Application\ShadowConfiguration;

final class ShadowConfigurationConfirmation
{
    public function build(
        ShadowConfigurationDetection $detection,
        array $preview,
        bool $confirmed,
    ): string {
        if (ShadowConfigurationIntent::Unknown === $detection->intent) {
            return 'I could not map that request to a configuration change yet.';
        }

        if (!$confirmed) {
            return sprintf(
                'I understood: %s Apply this change? (%s → %s)',
                $detection->explanation,
                $this->formatValue($preview['from'] ?? null),
                $this->formatValue($preview['to'] ?? null),
            );
        }

        return sprintf(
            'Applied %s. %s is now %s.',
            $detection->intent->value,
            (string) ($preview['field'] ?? 'setting'),
            $this->formatValue($preview['to'] ?? null),
        );
    }

    public function requiresConfirmation(ShadowConfigurationIntent $intent): bool
    {
        return ShadowConfigurationIntent::ResetProfile === $intent;
    }

    private function formatValue(mixed $value): string
    {
        if (is_float($value)) {
            return (string) $value;
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }

        return 'unchanged';
    }
}
