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

final class MockTranslationProvider implements TranslationProviderInterface
{
    public function translate(Transcript $transcript, TranslationLanguage $target): Translation
    {
        /** @var list<TranslationSegment> $segments */
        $segments = [];

        foreach ($transcript->segments()->all() as $segment) {
            $segments[] = TranslationSegment::create(
                $segment->index(),
                $segment->text(),
                sprintf('[%s] %s', $target->value, $segment->text()),
            );
        }

        return Translation::create(
            TranslationId::generate(),
            TranscriptLanguageMapper::toTranslationLanguage($transcript->language()),
            $target,
            TranslationProvider::Mock,
            new TranslationSegmentCollection($segments),
        );
    }
}
