from unittest.mock import MagicMock

import pytest

from app.generators.ArtifactGenerationRequest import ArtifactGenerationRequest
from app.generators.ArtifactGenerationResult import ArtifactGenerationResult
from app.generators.ArtifactGeneratorInterface import ArtifactGeneratorInterface
from app.generators.ArtifactType import (
    ARTIFACT_TYPE_FLASHCARDS,
    ARTIFACT_TYPE_QUIZ,
    ARTIFACT_TYPE_TIMELINE,
)
from app.generators.DeterministicSummaryGenerator import DeterministicSummaryGenerator
from app.generators.SummaryGeneratorInterface import SummaryGeneratorInterface
from app.models.ProcessingJob import ProcessingJob
from app.services.ProcessingService import ProcessingService


def _mock_artifact_factory(
    quiz_content: str = "# Quiz\n\n## Question 1\nGenerated quiz.",
    flashcards_content: str = (
        "# Flashcards\n\n## Card 1\n\nFront:\nTerm\n\nBack:\nDefinition"
    ),
    timeline_content: str = (
        "# Timeline\n\n## Ancient Rome\n\n- 753 BC — Foundation of Rome"
    ),
) -> tuple[MagicMock, MagicMock, MagicMock, MagicMock]:
    quiz_generator = MagicMock(spec=ArtifactGeneratorInterface)
    quiz_generator.generate.return_value = ArtifactGenerationResult(
        artifact_type=ARTIFACT_TYPE_QUIZ,
        content=quiz_content,
    )
    flashcards_generator = MagicMock(spec=ArtifactGeneratorInterface)
    flashcards_generator.generate.return_value = ArtifactGenerationResult(
        artifact_type=ARTIFACT_TYPE_FLASHCARDS,
        content=flashcards_content,
    )
    timeline_generator = MagicMock(spec=ArtifactGeneratorInterface)
    timeline_generator.generate.return_value = ArtifactGenerationResult(
        artifact_type=ARTIFACT_TYPE_TIMELINE,
        content=timeline_content,
    )

    def factory_side_effect(artifact_type: str) -> ArtifactGeneratorInterface:
        if artifact_type == ARTIFACT_TYPE_QUIZ:
            return quiz_generator
        if artifact_type == ARTIFACT_TYPE_FLASHCARDS:
            return flashcards_generator
        if artifact_type == ARTIFACT_TYPE_TIMELINE:
            return timeline_generator
        msg = f"Unexpected artifact type: {artifact_type!r}"
        raise ValueError(msg)

    factory = MagicMock(side_effect=factory_side_effect)
    return factory, quiz_generator, flashcards_generator, timeline_generator


@pytest.mark.asyncio
async def test_execute_creates_all_learning_artifacts() -> None:
    repository = MagicMock()
    document_extraction = MagicMock()
    transcript = (
        "The Roman Empire was vast. It lasted many centuries. "
        "Its legacy shaped Europe. Modern law still reflects Roman ideas."
    )
    document_extraction.extract_transcript.return_value = transcript
    summary_generator = DeterministicSummaryGenerator()
    artifact_factory, quiz_generator, flashcards_generator, timeline_generator = (
        _mock_artifact_factory()
    )
    service = ProcessingService(
        repository=repository,
        document_extraction=document_extraction,
        summary_generator=summary_generator,
        create_artifact_generator=artifact_factory,
        sleep_fn=_instant_sleep,
    )
    job = ProcessingJob(
        id="job-123",
        content_id="content-456",
        type="summary",
    )

    await service.execute(job)

    repository.start.assert_called_once_with("job-123")
    document_extraction.extract_transcript.assert_called_once_with("content-456")
    artifact_factory.assert_any_call(ARTIFACT_TYPE_QUIZ)
    artifact_factory.assert_any_call(ARTIFACT_TYPE_FLASHCARDS)
    artifact_factory.assert_any_call(ARTIFACT_TYPE_TIMELINE)
    quiz_generator.generate.assert_called_once_with(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_QUIZ,
            transcript=transcript,
        ),
    )
    flashcards_generator.generate.assert_called_once_with(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_FLASHCARDS,
            transcript=transcript,
        ),
    )
    timeline_generator.generate.assert_called_once_with(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_TIMELINE,
            transcript=transcript,
        ),
    )
    assert repository.create_artifact.call_count == 5
    repository.create_artifact.assert_any_call(
        "content-456",
        "job-123",
        "transcript",
        transcript,
    )
    repository.create_artifact.assert_any_call(
        "content-456",
        "job-123",
        "summary",
        "The Roman Empire was vast. It lasted many centuries. "
        "Its legacy shaped Europe.",
    )
    repository.create_artifact.assert_any_call(
        "content-456",
        "job-123",
        "quiz",
        "# Quiz\n\n## Question 1\nGenerated quiz.",
    )
    repository.create_artifact.assert_any_call(
        "content-456",
        "job-123",
        "flashcards",
        "# Flashcards\n\n## Card 1\n\nFront:\nTerm\n\nBack:\nDefinition",
    )
    repository.create_artifact.assert_any_call(
        "content-456",
        "job-123",
        "timeline",
        "# Timeline\n\n## Ancient Rome\n\n- 753 BC — Foundation of Rome",
    )
    repository.complete.assert_called_once_with("job-123")


@pytest.mark.asyncio
async def test_execute_uses_injected_summary_generator_interface() -> None:
    repository = MagicMock()
    document_extraction = MagicMock()
    document_extraction.extract_transcript.return_value = "Input transcript."
    summary_generator = MagicMock(spec=SummaryGeneratorInterface)
    summary_generator.generate.return_value = "Generated via interface."
    artifact_factory, _, _, _ = _mock_artifact_factory(
        quiz_content="# Quiz\n\nQuiz from factory.",
        flashcards_content="# Flashcards\n\nCard from factory.",
        timeline_content="# Timeline\n\nTimeline from factory.",
    )
    service = ProcessingService(
        repository=repository,
        document_extraction=document_extraction,
        summary_generator=summary_generator,
        create_artifact_generator=artifact_factory,
        sleep_fn=_instant_sleep,
    )
    job = ProcessingJob(
        id="job-123",
        content_id="content-456",
        type="summary",
    )

    await service.execute(job)

    summary_generator.generate.assert_called_once_with("Input transcript.")
    artifact_factory.assert_any_call(ARTIFACT_TYPE_QUIZ)
    artifact_factory.assert_any_call(ARTIFACT_TYPE_FLASHCARDS)
    artifact_factory.assert_any_call(ARTIFACT_TYPE_TIMELINE)
    repository.create_artifact.assert_any_call(
        "content-456",
        "job-123",
        "summary",
        "Generated via interface.",
    )
    repository.create_artifact.assert_any_call(
        "content-456",
        "job-123",
        "quiz",
        "# Quiz\n\nQuiz from factory.",
    )
    repository.create_artifact.assert_any_call(
        "content-456",
        "job-123",
        "flashcards",
        "# Flashcards\n\nCard from factory.",
    )
    repository.create_artifact.assert_any_call(
        "content-456",
        "job-123",
        "timeline",
        "# Timeline\n\nTimeline from factory.",
    )


@pytest.mark.asyncio
async def test_execute_uses_deterministic_summary_and_artifact_factory_by_default(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    repository = MagicMock()
    document_extraction = MagicMock()
    transcript = (
        "The Roman Empire was vast. It lasted many centuries. "
        "Its legacy shaped Europe."
    )
    document_extraction.extract_transcript.return_value = transcript
    monkeypatch.setenv("AI_PROVIDER", "mock")
    monkeypatch.setenv("AI_EXECUTION_MODE", "balanced")
    service = ProcessingService(
        repository=repository,
        document_extraction=document_extraction,
        sleep_fn=_instant_sleep,
    )
    job = ProcessingJob(
        id="job-123",
        content_id="content-456",
        type="summary",
    )

    await service.execute(job)

    repository.create_artifact.assert_any_call(
        "content-456",
        "job-123",
        "summary",
        "The Roman Empire was vast. It lasted many centuries. "
        "Its legacy shaped Europe.",
    )
    quiz_calls = [
        call
        for call in repository.create_artifact.call_args_list
        if call.args[2] == "quiz"
    ]
    flashcards_calls = [
        call
        for call in repository.create_artifact.call_args_list
        if call.args[2] == "flashcards"
    ]
    timeline_calls = [
        call
        for call in repository.create_artifact.call_args_list
        if call.args[2] == "timeline"
    ]
    assert len(quiz_calls) == 1
    assert len(flashcards_calls) == 1
    assert len(timeline_calls) == 1
    assert quiz_calls[0].args[3].startswith("Mock response:")
    assert flashcards_calls[0].args[3].startswith("Mock response:")
    assert timeline_calls[0].args[3].startswith("Mock response:")


@pytest.mark.asyncio
async def test_execute_uses_artifact_generator_factory_for_quiz() -> None:
    repository = MagicMock()
    document_extraction = MagicMock()
    document_extraction.extract_transcript.return_value = "Transcript text."
    summary_generator = MagicMock(spec=SummaryGeneratorInterface)
    summary_generator.generate.return_value = "Summary text."
    artifact_factory, quiz_generator, _, _ = _mock_artifact_factory()
    service = ProcessingService(
        repository=repository,
        document_extraction=document_extraction,
        summary_generator=summary_generator,
        create_artifact_generator=artifact_factory,
        sleep_fn=_instant_sleep,
    )
    job = ProcessingJob(
        id="job-123",
        content_id="content-456",
        type="summary",
    )

    await service.execute(job)

    artifact_factory.assert_any_call(ARTIFACT_TYPE_QUIZ)
    quiz_generator.generate.assert_called_once()
    assert quiz_generator.generate.call_args.args[0].artifact_type == ARTIFACT_TYPE_QUIZ


@pytest.mark.asyncio
async def test_execute_uses_artifact_generator_factory_for_flashcards() -> None:
    repository = MagicMock()
    document_extraction = MagicMock()
    document_extraction.extract_transcript.return_value = "Transcript text."
    summary_generator = MagicMock(spec=SummaryGeneratorInterface)
    summary_generator.generate.return_value = "Summary text."
    artifact_factory, _, flashcards_generator, _ = _mock_artifact_factory()
    service = ProcessingService(
        repository=repository,
        document_extraction=document_extraction,
        summary_generator=summary_generator,
        create_artifact_generator=artifact_factory,
        sleep_fn=_instant_sleep,
    )
    job = ProcessingJob(
        id="job-123",
        content_id="content-456",
        type="summary",
    )

    await service.execute(job)

    artifact_factory.assert_any_call(ARTIFACT_TYPE_FLASHCARDS)
    flashcards_generator.generate.assert_called_once()
    assert (
        flashcards_generator.generate.call_args.args[0].artifact_type
        == ARTIFACT_TYPE_FLASHCARDS
    )


@pytest.mark.asyncio
async def test_execute_uses_artifact_generator_factory_for_timeline() -> None:
    repository = MagicMock()
    document_extraction = MagicMock()
    document_extraction.extract_transcript.return_value = "Transcript text."
    summary_generator = MagicMock(spec=SummaryGeneratorInterface)
    summary_generator.generate.return_value = "Summary text."
    artifact_factory, _, _, timeline_generator = _mock_artifact_factory()
    service = ProcessingService(
        repository=repository,
        document_extraction=document_extraction,
        summary_generator=summary_generator,
        create_artifact_generator=artifact_factory,
        sleep_fn=_instant_sleep,
    )
    job = ProcessingJob(
        id="job-123",
        content_id="content-456",
        type="summary",
    )

    await service.execute(job)

    artifact_factory.assert_any_call(ARTIFACT_TYPE_TIMELINE)
    timeline_generator.generate.assert_called_once()
    assert (
        timeline_generator.generate.call_args.args[0].artifact_type
        == ARTIFACT_TYPE_TIMELINE
    )


@pytest.mark.asyncio
async def test_execute_skips_artifact_creation_for_non_summary_job() -> None:
    repository = MagicMock()
    document_extraction = MagicMock()
    service = ProcessingService(
        repository=repository,
        document_extraction=document_extraction,
        sleep_fn=_instant_sleep,
    )
    job = ProcessingJob(
        id="job-123",
        content_id="content-456",
        type="quiz",
    )

    await service.execute(job)

    document_extraction.extract_transcript.assert_not_called()
    repository.create_artifact.assert_not_called()
    repository.complete.assert_called_once_with("job-123")


async def _instant_sleep(_seconds: float) -> None:
    return None
