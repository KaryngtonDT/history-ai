from unittest.mock import MagicMock, patch

from app.repositories.SymfonyApiRepository import SymfonyApiRepository


def test_start_calls_internal_endpoint() -> None:
    response = MagicMock()
    response.raise_for_status = MagicMock()

    with patch("httpx.post", return_value=response) as mock_post:
        repository = SymfonyApiRepository(base_url="http://backend")
        repository.start("job-123")

    mock_post.assert_called_once_with(
        "http://backend/internal/processing-jobs/job-123/start",
        json=None,
        headers={"Accept": "application/json"},
        timeout=30.0,
    )


def test_update_progress_sends_json_body() -> None:
    response = MagicMock()
    response.raise_for_status = MagicMock()

    with patch("httpx.post", return_value=response) as mock_post:
        repository = SymfonyApiRepository(base_url="http://backend")
        repository.update_progress("job-123", 42)

    mock_post.assert_called_once_with(
        "http://backend/internal/processing-jobs/job-123/progress",
        json={"progress": 42},
        headers={"Accept": "application/json"},
        timeout=30.0,
    )
