<?php

declare(strict_types=1);

namespace App\Infrastructure\VoiceClone;

use App\Domain\Translation\Translation;
use App\Domain\TTS\AudioArtifact;
use App\Domain\TTS\AudioId;
use App\Domain\VoiceClone\VoiceCloneArtifact;
use App\Domain\VoiceClone\VoiceCloneArtifactId;
use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Domain\VoiceClone\VoiceCloneProviderInterface;
use App\Domain\VoiceClone\VoiceCloneReferenceContextInterface;
use App\Infrastructure\VoiceClone\Exception\OpenVoiceProviderException;

final class OpenVoiceProvider implements VoiceCloneProviderInterface
{
    public function __construct(
        private readonly OpenVoiceProcessRunnerInterface $processRunner,
        private readonly VoiceCloneMapper $voiceCloneMapper,
        private readonly VoiceCloneReferenceContextInterface $processingContext,
        private readonly string $binary,
        private readonly string $model,
        private readonly string $basePath,
        private readonly string $outputDirectory,
    ) {
    }

    public function cloneVoice(AudioArtifact $source, Translation $translation): VoiceCloneArtifact
    {
        $referencePath = $this->processingContext->referenceAudioPath();

        if (null === $referencePath || '' === trim($referencePath)) {
            throw new OpenVoiceProviderException('Reference audio path is required for voice cloning.');
        }

        if ('' === trim($source->storagePath())) {
            throw new OpenVoiceProviderException('Source audio storage path cannot be empty.');
        }

        $clonedAudioId = AudioId::generate();
        $artifactId = VoiceCloneArtifactId::generate();
        $outputPath = rtrim($this->outputDirectory, '/\\').DIRECTORY_SEPARATOR.$clonedAudioId->value.'.wav';

        $command = [
            $this->binary,
            '--reference',
            $referencePath,
            '--source',
            $source->storagePath(),
            '--text',
            $translation->text(),
            '--model',
            $this->model,
            '--base-path',
            $this->basePath,
            '--output',
            $outputPath,
            '--source-duration',
            (string) $source->duration(),
        ];

        $output = $this->processRunner->run($command);

        return $this->voiceCloneMapper->toArtifact(
            $output,
            $translation,
            VoiceCloneProvider::OpenVoice,
            $clonedAudioId,
            $artifactId,
            $source->audioId(),
            $outputPath,
        );
    }
}
