from unittest.mock import MagicMock

import pytest

from app.models.ProcessingJob import ProcessingJob
from app.services.ProcessingService import ProcessingService


@pytest.mark.asyncio
async def test_execute_creates_summary_artifact_before_complete() -> None:
    repository = MagicMock()
    service = ProcessingService(repository=repository, sleep_fn=_instant_sleep)
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
    repository.create_artifact.assert_called_once_with(
        "content-456",
        "job-123",
        "summary",
        ProcessingService.SUMMARY_ARTIFACT_CONTENT,
    )
    repository.complete.assert_called_once_with("job-123")


@pytest.mark.asyncio
async def test_execute_skips_artifact_creation_for_non_summary_job() -> None:
    repository = MagicMock()
    service = ProcessingService(repository=repository, sleep_fn=_instant_sleep)
    job = ProcessingJob(
        id="job-123",
        content_id="content-456",
        type="quiz",
    )

    await service.execute(job)

    repository.create_artifact.assert_not_called()
    repository.complete.assert_called_once_with("job-123")


async def _instant_sleep(_seconds: float) -> None:
    return None
