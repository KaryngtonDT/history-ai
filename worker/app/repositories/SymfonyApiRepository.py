import os
from dataclasses import dataclass
from typing import Any

import httpx


@dataclass(frozen=True)
class SymfonyApiRepository:
    base_url: str

    @classmethod
    def from_env(cls) -> "SymfonyApiRepository":
        return cls(base_url=os.environ.get("SYMFONY_API_BASE_URL", "http://backend").rstrip("/"))

    def start(self, job_id: str) -> None:
        self._post(f"/internal/processing-jobs/{job_id}/start")

    def update_progress(self, job_id: str, progress: int) -> None:
        self._post(
            f"/internal/processing-jobs/{job_id}/progress",
            json={"progress": progress},
        )

    def create_artifact(
        self,
        content_id: str,
        processing_job_id: str,
        artifact_type: str,
        content: str,
    ) -> dict[str, Any]:
        response = self._post(
            "/internal/artifacts",
            json={
                "contentId": content_id,
                "processingJobId": processing_job_id,
                "type": artifact_type,
                "content": content,
            },
        )
        payload = response.json()
        if not isinstance(payload, dict):
            msg = "Expected JSON object from artifact creation response."
            raise TypeError(msg)
        return payload

    def complete(self, job_id: str) -> None:
        self._post(f"/internal/processing-jobs/{job_id}/complete")

    def _post(self, path: str, json: dict | None = None) -> httpx.Response:
        response = httpx.post(
            f"{self.base_url}{path}",
            json=json,
            headers={"Accept": "application/json"},
            timeout=30.0,
        )
        response.raise_for_status()
        return response
