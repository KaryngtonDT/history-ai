from unittest.mock import AsyncMock, patch

from fastapi.testclient import TestClient

from app.main import app


def test_execute_job_returns_completed() -> None:
    client = TestClient(app)

    with patch(
        "app.api.jobs._worker.execute",
        new=AsyncMock(return_value=None),
    ):
        response = client.post(
            "/jobs/execute",
            json={
                "processingJobId": "job-123",
                "contentId": "content-456",
                "type": "summary",
            },
        )

    assert response.status_code == 200
    assert response.json() == {"status": "completed"}


def test_execute_job_passes_job_to_worker() -> None:
    client = TestClient(app)
    execute = AsyncMock(return_value=None)

    with patch("app.api.jobs._worker.execute", new=execute):
        client.post(
            "/jobs/execute",
            json={
                "processingJobId": "job-123",
                "contentId": "content-456",
                "type": "quiz",
            },
        )

    execute.assert_awaited_once()
    job = execute.await_args.args[0]
    assert job.id == "job-123"
    assert job.content_id == "content-456"
    assert job.type == "quiz"
