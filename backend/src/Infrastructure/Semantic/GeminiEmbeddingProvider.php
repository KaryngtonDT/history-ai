<?php

declare(strict_types=1);

namespace App\Infrastructure\Semantic;

use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddingProviderInterface;
use App\Domain\Semantic\EmbeddingVector;
use App\Domain\Semantic\Exception\InvalidEmbeddingVectorException;
use App\Infrastructure\Semantic\Exception\GeminiEmbeddingProviderException;
use JsonException;
use Throwable;

final class GeminiEmbeddingProvider implements EmbeddingProviderInterface
{
    public const string DEFAULT_MODEL = 'text-embedding-004';

    private const string API_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct(
        private readonly GeminiEmbeddingTransportInterface $transport,
        private readonly string $apiKey,
        private readonly string $model = self::DEFAULT_MODEL,
    ) {
    }

    public function generateEmbedding(ChunkText $text): EmbeddingVector
    {
        if ('' === trim($this->apiKey)) {
            throw new GeminiEmbeddingProviderException('GEMINI_API_KEY is not configured.');
        }

        $url = sprintf('%s/models/%s:embedContent', self::API_BASE_URL, $this->model);
        $payload = [
            'model' => sprintf('models/%s', $this->model),
            'content' => [
                'parts' => [
                    ['text' => $text->value()],
                ],
            ],
        ];
        $headers = [
            'Content-Type' => 'application/json',
            'x-goog-api-key' => $this->apiKey,
        ];

        try {
            $responseBody = $this->transport->post($url, $headers, $payload);
            /** @var mixed $decoded */
            $decoded = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw new GeminiEmbeddingProviderException(
                'Gemini embedding request failed.',
                0,
                $exception,
            );
        }

        return $this->mapResponseToVector($decoded);
    }

    private function mapResponseToVector(mixed $payload): EmbeddingVector
    {
        if (!is_array($payload)) {
            throw new GeminiEmbeddingProviderException('Gemini API returned an unexpected response payload.');
        }

        $embedding = $payload['embedding'] ?? null;
        if (!is_array($embedding)) {
            throw new GeminiEmbeddingProviderException('Gemini API returned no embedding.');
        }

        $values = $embedding['values'] ?? null;
        if (!is_array($values) || [] === $values) {
            throw new GeminiEmbeddingProviderException('Gemini API returned an empty embedding.');
        }

        try {
            /** @var list<float|int> $values */
            return new EmbeddingVector($values);
        } catch (InvalidEmbeddingVectorException $exception) {
            throw new GeminiEmbeddingProviderException(
                'Gemini API returned invalid embedding values.',
                0,
                $exception,
            );
        }
    }
}
