<?php

declare(strict_types=1);

namespace App\Application\ShadowVoice;

final readonly class ShadowVoiceDefinition
{
    /**
     * @param list<string> $supportedLanguages
     */
    public function __construct(
        private string $id,
        private string $name,
        private ShadowVoiceEngine $engine,
        private array $supportedLanguages,
        private string $gender,
        private string $accent,
        private string $quality,
        private string $latency,
        private string $previewText,
        private ShadowVoiceCollection $collection,
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function engine(): ShadowVoiceEngine
    {
        return $this->engine;
    }

    /**
     * @return list<string>
     */
    public function supportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    public function gender(): string
    {
        return $this->gender;
    }

    public function accent(): string
    {
        return $this->accent;
    }

    public function quality(): string
    {
        return $this->quality;
    }

    public function latency(): string
    {
        return $this->latency;
    }

    public function previewText(): string
    {
        return $this->previewText;
    }

    public function collection(): ShadowVoiceCollection
    {
        return $this->collection;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'engine' => $this->engine->value,
            'engineLabel' => $this->engine->label(),
            'supportedLanguages' => $this->supportedLanguages,
            'gender' => $this->gender,
            'accent' => $this->accent,
            'quality' => $this->quality,
            'latency' => $this->latency,
            'preview' => $this->previewText,
            'collection' => $this->collection->value,
            'collectionLabel' => $this->collection->label(),
            'available' => $this->engine->isAvailable(),
        ];
    }
}
