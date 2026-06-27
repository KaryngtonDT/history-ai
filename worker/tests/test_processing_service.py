from unittest.mock import MagicMock

import pytest

from app.generators.ArtifactGenerationRequest import ArtifactGenerationRequest
from app.generators.ArtifactGenerationResult import ArtifactGenerationResult
from app.generators.ArtifactGeneratorInterface import ArtifactGeneratorInterface
from app.generators.ArtifactType import ARTIFACT_TYPE_QUIZ
from app.generators.DeterministicSummaryGenerator import DeterministicSummaryGenerator
from app.generators.SummaryGeneratorInterface import SummaryGeneratorInterface
from app.models.ProcessingJob import ProcessingJob
from app.services.ProcessingService import ProcessingService


def _mock_quiz_factory(
    quiz_content: str = "# Quiz\n\n## Question 1\nGenerated quiz.",
) -> tuple[MagicMock, MagicMock]:
    quiz_generator = MagicMock(spec=ArtifactGeneratorInterface)
    quiz_generator.generate.return_value = ArtifactGenerationResult(
        artifact_type=ARTIFACT_TYPE_QUIZ,
        content=quiz_content,
    )
    factory = MagicMock(side_effect=lambda artifact_type: quiz_generator)
    return factory, quiz_generator


@pytest.mark.asyncio
async def test_execute_creates_transcript_summary_and_quiz_artifacts() -> None:
    repository = MagicMock()
    document_extraction = MagicMock()
    transcript = (
        "The Roman Empire was vast. It lasted many centuries. "
        "Its legacy shaped Europe. Modern law still reflects Roman ideas."
    )
    document_extraction.extract_transcript.return_value = transcript
    summary_generator = DeterministicSummaryGenerator()
    quiz_factory, quiz_generator = _mock_quiz_factory()
    service = ProcessingService(
        repository=repository,
        document_extraction=document_extraction,
        summary_generator=summary_generator,
        create_artifact_generator=quiz_factory,
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
    quiz_factory.assert_called_once_with(ARTIFACT_TYPE_QUIZ)
    quiz_generator.generate.assert_called_once_with(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_QUIZ,
            transcript=transcript,
        ),
    )
    assert repository.create_artifact.call_count == 3
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
    repository.complete.assert_called_once_with("job-123")


@pytest.mark.asyncio
async def test_execute_uses_injected_summary_generator_interface() -> None:
    repository = MagicMock()
    document_extraction = MagicMock()
    document_extraction.extract_transcript.return_value = "Input transcript."
    summary_generator = MagicMock(spec=SummaryGeneratorInterface)
    summary_generator.generate.return_value = "Generated via interface."
    quiz_factory, _quiz_generator = _mock_quiz_factory(
        quiz_content="# Quiz\n\nQuiz from factory.",
    )
    service = ProcessingService(
        repository=repository,
        document_extraction=document_extraction,
        summary_generator=summary_generator,
        create_artifact_generator=quiz_factory,
        sleep_fn=_instant_sleep,
    )
    job = ProcessingJob(
        id="job-123",
        content_id="content-456",
        type="summary",
    )

    await service.execute(job)

    summary_generator.generate.assert_called_once_with("Input transcript.")
    quiz_factory.assert_called_once_with(ARTIFACT_TYPE_QUIZ)
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


@pytest.mark.asyncio
async def test_execute_uses_deterministic_summary_and_quiz_factory_by_default(
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
    assert len(quiz_calls) == 1
    assert quiz_calls[0].args[3].startswith("Mock response:")


@pytest.mark.asyncio
async def test_execute_uses_artifact_generator_factory_for_quiz() -> None:
    repository = MagicMock()
    document_extraction = MagicMock()
    document_extraction.extract_transcript.return_value = "Transcript text."
    summary_generator = MagicMock(spec=SummaryGeneratorInterface)
    summary_generator.generate.return_value = "Summary text."
    quiz_factory, quiz_generator = _mock_quiz_factory()
    service = ProcessingService(
        repository=repository,
        document_extraction=document_extraction,
        summary_generator=summary_generator,
        create_artifact_generator=quiz_factory,
        sleep_fn=_instant_sleep,
    )
    job = ProcessingJob(
        id="job-123",
        content_id="content-456",
        type="summary",
    )

    await service.execute(job)

    quiz_factory.assert_called_once_with(ARTIFACT_TYPE_QUIZ)
    quiz_generator.generate.assert_called_once()
    assert quiz_generator.generate.call_args.args[0].artifact_type == ARTIFACT_TYPE_QUIZ


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
