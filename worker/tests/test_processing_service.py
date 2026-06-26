from unittest.mock import MagicMock

import pytest

from app.services.ProcessingService import ProcessingService


@pytest.mark.asyncio
async def test_execute_runs_start_progress_and_complete() -> None:
    repository = MagicMock()
    service = ProcessingService(repository=repository, sleep_fn=_instant_sleep)

    await service.execute("job-123")

    repository.start.assert_called_once_with("job-123")
    repository.update_progress.assert_any_call("job-123", 20)
    repository.update_progress.assert_any_call("job-123", 45)
    repository.update_progress.assert_any_call("job-123", 80)
    assert repository.update_progress.call_count == 3
    repository.complete.assert_called_once_with("job-123")


async def _instant_sleep(_seconds: float) -> None:
    return None
