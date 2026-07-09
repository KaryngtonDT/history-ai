<?php

declare(strict_types=1);

namespace App\Infrastructure\Translation;

use App\Domain\Speech\Transcript;
use App\Domain\Translation\Translation;
use App\Domain\Translation\TranslationId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationProviderInterface;
use App\Domain\Translation\TranslationSegment;
use App\Domain\Translation\TranslationSegmentCollection;
use App\Infrastructure\Translation\Exception\OllamaTranslationException;

final class OllamaTranslationProvider implements TranslationProviderInterface
{
    public function __construct(
        private readonly OllamaClientInterface $client,
        private readonly OllamaTranslationPromptBuilder $promptBuilder,
        private readonly string $model,
    ) {
    }

    public function translate(Transcript $transcript, TranslationLanguage $target): Translation
    {
        if ($transcript->segments()->isEmpty()) {
            throw new OllamaTranslationException('Cannot translate an empty transcript.');
        }

        $prompt = $this->promptBuilder->build($transcript, $target);

        $response = $this->client->generate([
            'model' => $this->model,
            'prompt' => $prompt,
            'stream' => false,
            'format' => 'json',
            'think' => false,
        ]);

        $responseText = $response['response'] ?? null;

        if (!is_string($responseText) || '' === trim($responseText)) {
            throw new OllamaTranslationException('Ollama response did not include translated text.');
        }

        $mappedSegments = $this->promptBuilder->mapResponseToSegments($transcript, trim($responseText));

        /** @var list<TranslationSegment> $segments */
        $segments = [];

        foreach ($mappedSegments as $segment) {
            $segments[] = TranslationSegment::create(
                $segment['index'],
                $segment['sourceText'],
                $segment['translatedText'],
            );
        }

        return Translation::create(
            TranslationId::generate(),
            TranscriptLanguageMapper::toTranslationLanguage($transcript->language()),
            $target,
            TranslationProvider::Qwen,
            new TranslationSegmentCollection($segments),
        );
    }
}
