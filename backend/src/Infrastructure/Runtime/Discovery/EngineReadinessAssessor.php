<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Discovery;

use App\Domain\Engine\Engine;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Engine\EngineFamily;
use App\Domain\Engine\EngineRequirement;
use App\Domain\Engine\EngineVersion;
use App\Domain\Runtime\EngineExecutionMode;
use App\Domain\Runtime\RuntimeStatus;
use App\Infrastructure\Runtime\Catalog\EngineDefinition;
use App\Infrastructure\Runtime\Catalog\EngineCatalogDefinitions;

final class EngineReadinessAssessor
{
    public function __construct(
        private readonly BinaryScanner $binaryScanner,
        private readonly ModelScanner $modelScanner,
        private readonly OllamaScanner $ollamaScanner,
        private readonly string $sttConfiguredModel,
        private readonly string $ollamaConfiguredModel,
        private readonly string $sttProvider,
        private readonly string $translationProvider,
        private readonly string $ttsProvider,
        private readonly string $voiceCloneProvider,
        private readonly string $lipSyncProvider,
        private readonly string $videoRenderProvider,
    ) {
    }

    public function assess(EngineDefinition $definition): Engine
    {
        if (null !== $definition->ollamaModelTag) {
            return $this->assessOllamaEngine($definition);
        }

        if (EngineFamily::Ffmpeg === $definition->family && null !== $definition->expectedModel) {
            return $this->assessFfmpegVariant($definition);
        }

        return $this->assessBinaryEngine($definition);
    }

    private function assessOllamaEngine(EngineDefinition $definition): Engine
    {
        $apiReady = $this->ollamaScanner->isApiAvailable();
        $tag = (string) $definition->ollamaModelTag;
        $modelReady = $this->ollamaScanner->hasModel($tag);
        $configured = $this->isConfiguredEngine($definition);
        $requirements = [
            new EngineRequirement('ollama_api', 'Ollama API reachable', $apiReady),
            new EngineRequirement(
                'ollama_model',
                'Model tag '.$tag,
                $modelReady,
                $modelReady ? implode(', ', $this->ollamaScanner->listModels()) : null,
            ),
        ];

        $configurationMismatch = null;
        if ($configured && $apiReady && !$modelReady) {
            $configurationMismatch = sprintf(
                'Required Ollama model tag "%s" is not installed. Run `ollama pull %s` (active OLLAMA_MODEL=%s).',
                $tag,
                $this->ollamaConfiguredModel,
                $this->ollamaConfiguredModel,
            );
        }

        [$status, $mode, $errorReason, $installed] = $this->resolveStatus(
            executableFound: $apiReady,
            modelFound: $modelReady,
            configured: $configured,
            catalogMode: EngineExecutionMode::Real,
            isShimBinary: false,
            modelRequired: true,
            configurationMismatch: $configurationMismatch,
        );

        return $this->buildEngine(
            $definition,
            $installed,
            $status,
            $mode,
            $apiReady,
            $modelReady,
            $configured,
            $requirements,
            null,
            $errorReason,
        );
    }

    private function assessFfmpegVariant(EngineDefinition $definition): Engine
    {
        $binaryPath = null !== $definition->binaryName
            ? $this->binaryScanner->locate($definition->binaryName)
            : null;
        $encoder = (string) $definition->expectedModel;
        $encoderReady = null !== $binaryPath && $this->ffmpegHasEncoder($binaryPath, $encoder);
        $configured = $this->isConfiguredEngine($definition);
        $requirements = [
            new EngineRequirement('binary', 'ffmpeg', null !== $binaryPath, $binaryPath),
            new EngineRequirement('encoder', $encoder, $encoderReady),
        ];

        [$status, $mode, $errorReason, $installed] = $this->resolveStatus(
            executableFound: null !== $binaryPath,
            modelFound: $encoderReady,
            configured: $configured,
            catalogMode: EngineExecutionMode::Real,
            isShimBinary: false,
            modelRequired: true,
            configurationMismatch: null !== $binaryPath && !$encoderReady
                ? sprintf('FFmpeg encoder "%s" not available in this build.', $encoder)
                : null,
        );

        return $this->buildEngine(
            $definition,
            $installed,
            $status,
            $mode,
            null !== $binaryPath,
            $encoderReady,
            $configured,
            $requirements,
            null !== $binaryPath ? new EngineVersion('detected', $binaryPath) : null,
            $errorReason,
        );
    }

    private function assessBinaryEngine(EngineDefinition $definition): Engine
    {
        $binaryPath = null !== $definition->binaryName
            ? $this->binaryScanner->locate($definition->binaryName)
            : null;
        $isShimBinary = null !== $binaryPath && $this->isShimBinary($binaryPath, $definition);
        $modelFound = false;

        if (null !== $definition->modelPath && $definition->requiresModelFiles) {
            $modelFound = $this->modelScanner->hasUsableContent($definition->modelPath);
        } elseif ($definition->usesHuggingFaceCache) {
            $modelFound = $this->sttConfiguredModel === $definition->expectedModel;
        } elseif (!$definition->requiresModelFiles) {
            $modelFound = true;
        }

        $configured = $this->isConfiguredEngine($definition);
        $configurationMismatch = null;

        if ($definition->usesHuggingFaceCache && null !== $definition->expectedModel && $configured) {
            if ($this->sttConfiguredModel !== $definition->expectedModel) {
                $configurationMismatch = sprintf(
                    'STT_FASTER_WHISPER_MODEL is "%s" but default engine expects "%s".',
                    $this->sttConfiguredModel,
                    $definition->expectedModel,
                );
                $modelFound = false;
            }
        }

        $requirements = [];

        if (null !== $definition->binaryName) {
            $requirements[] = new EngineRequirement('binary', $definition->binaryName, null !== $binaryPath, $binaryPath);
        }

        if (null !== $definition->modelPath && $definition->requiresModelFiles) {
            $requirements[] = new EngineRequirement(
                'models',
                'Model files in '.$definition->modelPath,
                $modelFound,
                $this->modelScanner->resolvePath($definition->modelPath),
            );
        }

        if ($definition->usesHuggingFaceCache && null !== $definition->expectedModel) {
            $requirements[] = new EngineRequirement(
                'model_config',
                'Configured model '.$definition->expectedModel,
                $this->sttConfiguredModel === $definition->expectedModel,
                $this->sttConfiguredModel,
            );
        }

        $catalogMode = $isShimBinary ? EngineExecutionMode::Shim : $definition->installedMode;

        [$status, $mode, $errorReason, $installed] = $this->resolveStatus(
            executableFound: null !== $binaryPath,
            modelFound: $modelFound,
            configured: $configured,
            catalogMode: $catalogMode,
            isShimBinary: $isShimBinary,
            modelRequired: $definition->requiresModelFiles || $definition->usesHuggingFaceCache,
            configurationMismatch: $configurationMismatch,
        );

        return $this->buildEngine(
            $definition,
            $installed,
            $status,
            $mode,
            null !== $binaryPath,
            $modelFound,
            $configured,
            $requirements,
            null !== $binaryPath ? new EngineVersion('detected', $binaryPath) : null,
            $errorReason,
        );
    }

    /**
     * @return array{0: RuntimeStatus, 1: EngineExecutionMode, 2: ?string, 3: bool}
     */
    private function resolveStatus(
        bool $executableFound,
        bool $modelFound,
        bool $configured,
        EngineExecutionMode $catalogMode,
        bool $isShimBinary,
        bool $modelRequired,
        ?string $configurationMismatch,
    ): array {
        if (!$executableFound) {
            return [RuntimeStatus::Missing, EngineExecutionMode::Real, 'Executable not found on PATH.', false];
        }

        if ($isShimBinary || EngineExecutionMode::Shim === $catalogMode) {
            return [
                RuntimeStatus::Mock,
                EngineExecutionMode::Shim,
                'Docker shim detected — produces placeholder artifacts, not real inference.',
                false,
            ];
        }

        if (null !== $configurationMismatch) {
            return [RuntimeStatus::Misconfigured, EngineExecutionMode::Real, $configurationMismatch, false];
        }

        if ($modelRequired && !$modelFound) {
            return [
                RuntimeStatus::Misconfigured,
                EngineExecutionMode::Real,
                'Required model files or model configuration are missing.',
                false,
            ];
        }

        return [RuntimeStatus::Ready, EngineExecutionMode::Real, null, true];
    }

    private function buildEngine(
        EngineDefinition $definition,
        bool $installed,
        RuntimeStatus $status,
        EngineExecutionMode $mode,
        bool $executableFound,
        bool $modelFound,
        bool $configured,
        array $requirements,
        ?EngineVersion $version,
        ?string $errorReason,
    ): Engine {
        return new Engine(
            id: $definition->id,
            displayName: $definition->displayName,
            capability: $definition->capability,
            family: $definition->family,
            role: $definition->role,
            installed: $installed,
            compatible: true,
            version: $version,
            binaryName: $definition->binaryName,
            modelPath: null !== $definition->modelPath
                ? $this->modelScanner->resolvePath($definition->modelPath)
                : null,
            requirements: $requirements,
            documentationUrl: $definition->documentationUrl,
            executionMode: $mode,
            runtimeStatus: $status,
            executableFound: $executableFound,
            modelFound: $modelFound,
            configured: $configured,
            errorReason: $errorReason,
            expectedModel: $definition->expectedModel,
            ollamaModelTag: $definition->ollamaModelTag,
        );
    }

    private function isConfiguredEngine(EngineDefinition $definition): bool
    {
        $activeId = $this->resolveActiveEngineId($definition->capability);

        return $activeId === $definition->id;
    }

    private function matchesConfiguredOllamaEngine(EngineDefinition $definition): bool
    {
        if (!$this->isConfiguredEngine($definition)) {
            return false;
        }

        $configured = strtolower($this->ollamaConfiguredModel);

        return str_contains($configured, strtolower((string) $definition->ollamaModelTag));
    }

    private function resolveActiveEngineId(EngineCatalogCapability $capability): string
    {
        return match ($capability) {
            EngineCatalogCapability::SpeechToText => $this->normalizeEngineId($this->sttProvider, [
                'faster_whisper' => 'faster_whisper_large_v3',
                'faster_whisper_large_v3' => 'faster_whisper_large_v3',
            ], 'faster_whisper_large_v3'),
            EngineCatalogCapability::Translation => $this->resolveActiveOllamaEngineId(),
            EngineCatalogCapability::TextToSpeech => $this->normalizeEngineId($this->ttsProvider, [
                'f5' => 'f5_tts',
                'f5_tts' => 'f5_tts',
            ], 'f5_tts'),
            EngineCatalogCapability::VoiceClone => $this->normalizeEngineId($this->voiceCloneProvider, [
                'openvoice' => 'openvoice_v2',
                'openvoice_v2' => 'openvoice_v2',
            ], 'openvoice_v2'),
            EngineCatalogCapability::LipSync => $this->normalizeEngineId($this->lipSyncProvider, [
                'latentsync' => 'latentsync',
            ], 'latentsync'),
            EngineCatalogCapability::VideoRender => $this->normalizeEngineId($this->videoRenderProvider, [
                'ffmpeg' => 'ffmpeg',
                'ffmpeg_nvenc' => 'ffmpeg_nvenc',
                'ffmpeg_av1' => 'ffmpeg_av1',
            ], 'ffmpeg'),
        };
    }

    /**
     * @param array<string, string> $map
     */
    private function normalizeEngineId(string $provider, array $map, string $fallback): string
    {
        $normalized = strtolower(trim($provider));

        return $map[$normalized] ?? $fallback;
    }

    private function resolveActiveOllamaEngineId(): string
    {
        $model = strtolower($this->ollamaConfiguredModel);

        if (str_contains($model, 'gemma')) {
            return 'ollama_gemma3';
        }

        if (str_contains($model, 'qwen')) {
            return 'ollama_qwen3';
        }

        if (str_contains($model, 'deepseek')) {
            return 'ollama_deepseek_r1_distill';
        }

        return 'ollama_gemma3';
    }

    private function isShimBinary(string $binaryPath, EngineDefinition $definition): bool
    {
        if (EngineExecutionMode::Shim === $definition->installedMode) {
            return true;
        }

        if (!is_readable($binaryPath)) {
            return false;
        }

        $head = @file_get_contents($binaryPath, false, null, 0, 4096);

        return is_string($head) && str_contains(strtolower($head), 'shim');
    }

    private function ffmpegHasEncoder(string $binaryPath, string $encoder): bool
    {
        $command = escapeshellarg($binaryPath).' -hide_banner -encoders 2>/dev/null';
        $output = shell_exec($command);

        return is_string($output) && str_contains($output, $encoder);
    }
}
