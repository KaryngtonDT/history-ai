<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shadow;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowAnswer;
use App\Domain\Shadow\ShadowInteraction;
use App\Domain\Shadow\ShadowInteractionCollection;
use App\Domain\Shadow\ShadowInteractionKind;
use App\Domain\Shadow\ShadowPlaybackState;
use App\Domain\Shadow\ShadowQuestion;
use App\Domain\Shadow\ShadowSession;
use App\Domain\Shadow\ShadowSessionId;
use App\Domain\Shadow\ShadowTimestamp;
use App\Domain\Video\VideoId;
use PHPUnit\Framework\TestCase;

final class ShadowSessionTest extends TestCase
{
    public function testStartSessionDefaults(): void
    {
        $session = ShadowSession::start(
            ShadowSessionId::generate(),
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
            'fr',
        );

        self::assertSame(ShadowPlaybackState::Playing, $session->playbackState());
        self::assertSame(0.0, $session->currentTimestamp()->seconds());
        self::assertSame('fr', $session->targetLanguage());
        self::assertTrue($session->interactions()->isEmpty());
    }

    public function testPauseAndResumeTransitions(): void
    {
        $session = ShadowSession::start(
            ShadowSessionId::generate(),
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
            'fr',
        )->withTimestamp(ShadowTimestamp::fromSeconds(42.5));

        $paused = $session->pause();
        self::assertSame(ShadowPlaybackState::Paused, $paused->playbackState());
        self::assertSame(1, $paused->interactions()->count());
        self::assertSame(
            ShadowInteractionKind::Pause,
            $paused->interactions()->all()[0]->kind(),
        );

        $resumed = $paused->resume();
        self::assertSame(ShadowPlaybackState::Playing, $resumed->playbackState());
        self::assertSame(2, $resumed->interactions()->count());
    }

    public function testCannotPauseWhenAlreadyPaused(): void
    {
        $session = ShadowSession::start(
            ShadowSessionId::generate(),
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
            'fr',
        )->pause();

        $this->expectException(InvalidShadowSessionException::class);
        $session->pause();
    }

    public function testRecordQuestionAndAnswer(): void
    {
        $session = ShadowSession::start(
            ShadowSessionId::generate(),
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
            'de',
        )->withTimestamp(ShadowTimestamp::fromSeconds(10.0));

        $withQuestion = $session->recordQuestion(
            ShadowQuestion::fromString('What does compound interest mean?'),
        );
        $withAnswer = $withQuestion->recordAnswer(
            ShadowAnswer::fromString('Compound interest grows on prior interest.'),
        );

        self::assertSame(2, $withAnswer->interactions()->count());
        self::assertSame(
            'What does compound interest mean?',
            $withAnswer->interactions()->all()[0]->question()?->text(),
        );
        self::assertSame(
            'Compound interest grows on prior interest.',
            $withAnswer->interactions()->all()[1]->answer()?->text(),
        );
    }

    public function testEndSession(): void
    {
        $session = ShadowSession::start(
            ShadowSessionId::generate(),
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
            'en',
        )->end();

        self::assertSame(ShadowPlaybackState::Ended, $session->playbackState());
    }
}
