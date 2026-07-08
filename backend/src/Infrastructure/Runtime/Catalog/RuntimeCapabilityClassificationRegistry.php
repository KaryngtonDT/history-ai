<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Catalog;

use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Runtime\RuntimeCapability;
use App\Domain\Runtime\RuntimeCapabilityClassification;
use App\Domain\Runtime\RuntimeCapabilityClassificationMeta;

final class RuntimeCapabilityClassificationRegistry
{
    /**
     * @return RuntimeCapabilityClassificationMeta
     */
    public static function for(EngineCatalogCapability|RuntimeCapability $capability): RuntimeCapabilityClassificationMeta
    {
        $runtimeCapability = $capability instanceof RuntimeCapability
            ? $capability
            : RuntimeCapability::from($capability->value);

        return self::definitions()[$runtimeCapability->value];
    }

    /**
     * @return list<RuntimeCapabilityClassificationMeta>
     */
    public static function all(): array
    {
        return array_values(self::definitions());
    }

    /**
     * @return list<RuntimeCapabilityClassificationMeta>
     */
    public static function byClassification(RuntimeCapabilityClassification $classification): array
    {
        return array_values(array_filter(
            self::all(),
            static fn (RuntimeCapabilityClassificationMeta $meta): bool => $meta->classification === $classification,
        ));
    }

    /**
     * @return array<string, RuntimeCapabilityClassificationMeta>
     */
    private static function definitions(): array
    {
        static $cache = null;

        if (null !== $cache) {
            return $cache;
        }

        $core = static fn (
            RuntimeCapability $capability,
            bool $hardwareDependent = false,
        ): RuntimeCapabilityClassificationMeta => new RuntimeCapabilityClassificationMeta(
            capability: $capability,
            classification: RuntimeCapabilityClassification::Core,
            required: true,
            enabledByDefault: true,
            hardwareDependent: $hardwareDependent,
            installable: true,
            recommended: true,
        );

        $optional = static fn (RuntimeCapability $capability): RuntimeCapabilityClassificationMeta => new RuntimeCapabilityClassificationMeta(
            capability: $capability,
            classification: RuntimeCapabilityClassification::Optional,
            required: false,
            enabledByDefault: false,
            hardwareDependent: false,
            installable: true,
            recommended: false,
        );

        $cache = [
            RuntimeCapability::SpeechToText->value => $core(RuntimeCapability::SpeechToText),
            RuntimeCapability::Translation->value => $core(RuntimeCapability::Translation),
            RuntimeCapability::TextToSpeech->value => $core(RuntimeCapability::TextToSpeech, true),
            RuntimeCapability::VoiceClone->value => $core(RuntimeCapability::VoiceClone),
            RuntimeCapability::VideoRender->value => $core(RuntimeCapability::VideoRender),
            RuntimeCapability::LipSync->value => new RuntimeCapabilityClassificationMeta(
                capability: RuntimeCapability::LipSync,
                classification: RuntimeCapabilityClassification::Premium,
                required: false,
                enabledByDefault: true,
                hardwareDependent: true,
                installable: true,
                recommended: true,
            ),
            RuntimeCapability::Ocr->value => $optional(RuntimeCapability::Ocr),
            RuntimeCapability::Vision->value => $optional(RuntimeCapability::Vision),
            RuntimeCapability::Embeddings->value => $optional(RuntimeCapability::Embeddings),
            RuntimeCapability::Reranking->value => $optional(RuntimeCapability::Reranking),
        ];

        return $cache;
    }
}
