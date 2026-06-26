import os
from dataclasses import dataclass

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

    def complete(self, job_id: str) -> None:
        self._post(f"/internal/processing-jobs/{job_id}/complete")

    def _post(self, path: str, json: dict | None = None) -> None:
        response = httpx.post(
            f"{self.base_url}{path}",
            json=json,
            headers={"Accept": "application/json"},
            timeout=30.0,
        )
        response.raise_for_status()
