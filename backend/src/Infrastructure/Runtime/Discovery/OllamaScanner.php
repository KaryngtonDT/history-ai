<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Discovery;

final class OllamaScanner
{
    public function __construct(private readonly string $baseUrl)
    {
    }

    public function isAvailable(): bool
    {
        return [] !== $this->listModels();
    }

    /**
     * @return list<string>
     */
    public function listModels(): array
    {
        $url = rtrim($this->baseUrl, '/').'/api/tags';
        $body = $this->fetch($url);

        if (null === $body) {
            return [];
        }

        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        $models = $decoded['models'] ?? [];

        if (!is_array($models)) {
            return [];
        }

        $names = [];

        foreach ($models as $model) {
            if (is_array($model) && is_string($model['name'] ?? null)) {
                $names[] = $model['name'];
            }
        }

        return $names;
    }

    public function hasModel(string $model): bool
    {
        foreach ($this->listModels() as $installed) {
            if ($installed === $model || str_starts_with($installed, $model.':')) {
                return true;
            }
        }

        return false;
    }

    private function fetch(string $url): ?string
    {
        if (function_exists('curl_init')) {
            $handle = curl_init($url);
            curl_setopt_array($handle, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 3,
            ]);
            $body = curl_exec($handle);
            $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);

            if (200 === $status && is_string($body)) {
                return $body;
            }
        }

        $context = stream_context_create(['http' => ['timeout' => 3, 'ignore_errors' => true]]);
        $body = @file_get_contents($url, false, $context);

        return is_string($body) ? $body : null;
    }
}
