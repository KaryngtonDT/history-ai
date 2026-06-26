import os
from pathlib import Path


class PdfNotFoundError(Exception):
    """Raised when no PDF can be resolved for a content id."""


class LocalPdfLoader:
    def __init__(
        self,
        storage_dir: Path,
        fallback_path: Path | None = None,
    ) -> None:
        self._storage_dir = storage_dir
        self._fallback_path = fallback_path

    @classmethod
    def from_env(cls) -> "LocalPdfLoader":
        storage_dir = Path(
            os.environ.get("PDF_STORAGE_DIR", "/app/storage"),
        )
        fallback_raw = os.environ.get("PDF_FALLBACK_PATH")
        fallback_path = (
            Path(fallback_raw)
            if fallback_raw
            else Path(__file__).resolve().parents[2] / "fixtures" / "sample.pdf"
        )

        return cls(storage_dir=storage_dir, fallback_path=fallback_path)

    def load(self, content_id: str) -> bytes:
        content_path = self._storage_dir / f"{content_id}.pdf"

        if content_path.is_file():
            return content_path.read_bytes()

        if self._fallback_path is not None and self._fallback_path.is_file():
            return self._fallback_path.read_bytes()

        msg = f"No PDF found for content id {content_id}."
        raise PdfNotFoundError(msg)
