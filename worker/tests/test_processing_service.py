from unittest.mock import MagicMock

import pytest

from app.generators.DeterministicSummaryGenerator import DeterministicSummaryGenerator
from app.generators.SummaryGeneratorInterface import SummaryGeneratorInterface
from app.models.ProcessingJob import ProcessingJob
from app.services.ProcessingService import ProcessingService


@pytest.mark.asyncio
async def test_execute_creates_summary_from_transcript_not_placeholder() -> None:
    repository = MagicMock()
    document_extraction = MagicMock()
    transcript = (
        "The Roman Empire was vast. It lasted many centuries. "
        "Its legacy shaped Europe. Modern law still reflects Roman ideas."
    )
    document_extraction.extract_transcript.return_value = transcript
    summary_generator = DeterministicSummaryGenerator()
    service = ProcessingService(
        repository=repository,
        document_extraction=document_extraction,
        summary_generator=summary_generator,
        sleep_fn=_instant_sleep,
    )
    job = ProcessingJob(
        id="job-123",
        content_id="content-456",
        type="summary",
    )

    await service.execute(job)

    repository.start.assert_called_once_with("job-123")
    repository.update_progress.assert_any_call("job-123", 20)
    repository.update_progress.assert_any_call("job-123", 45)
    repository.update_progress.assert_any_call("job-123", 80)
    assert repository.update_progress.call_count == 3
    document_extraction.extract_transcript.assert_called_once_with("content-456")
    assert repository.create_artifact.call_count == 2
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
    repository.complete.assert_called_once_with("job-123")


@pytest.mark.asyncio
async def test_execute_uses_injected_summary_generator_interface() -> None:
    repository = MagicMock()
    document_extraction = MagicMock()
    document_extraction.extract_transcript.return_value = "Input transcript."
    summary_generator = MagicMock(spec=SummaryGeneratorInterface)
    summary_generator.generate.return_value = "Generated via interface."
    service = ProcessingService(
        repository=repository,
        document_extraction=document_extraction,
        summary_generator=summary_generator,
        sleep_fn=_instant_sleep,
    )
    job = ProcessingJob(
        id="job-123",
        content_id="content-456",
        type="summary",
    )

    await service.execute(job)

    summary_generator.generate.assert_called_once_with("Input transcript.")
    repository.create_artifact.assert_any_call(
        "content-456",
        "job-123",
        "summary",
        "Generated via interface.",
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
