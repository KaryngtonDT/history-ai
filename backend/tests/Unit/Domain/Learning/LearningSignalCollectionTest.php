<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Learning;

use App\Domain\Learning\LearningSignal;
use App\Domain\Learning\LearningSignalCollection;
use App\Domain\Learning\LearningSignalType;
use PHPUnit\Framework\TestCase;

final class LearningSignalCollectionTest extends TestCase
{
    public function testFiltersByTypeAndReturnsRecent(): void
    {
        $collection = LearningSignalCollection::empty()
            ->append(LearningSignal::record(
                LearningSignalType::ShadowQuestionAsked,
                ['summary' => 'first'],
            ))
            ->append(LearningSignal::record(
                LearningSignalType::RepeatedVocabulary,
                ['summary' => 'second'],
            ))
            ->append(LearningSignal::record(
                LearningSignalType::RepeatedVocabulary,
                ['summary' => 'third'],
            ));

        self::assertSame(3, $collection->count());
        self::assertSame(2, $collection->ofType(LearningSignalType::RepeatedVocabulary)->count());
        self::assertSame('third', $collection->recent(1)[0]->context()['summary']);
    }
}
