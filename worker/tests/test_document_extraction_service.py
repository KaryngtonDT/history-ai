from pathlib import Path
from unittest.mock import MagicMock

import pytest

from app.extractors.PdfExtractor import PdfExtractor
from app.loaders.LocalPdfLoader import LocalPdfLoader, PdfNotFoundError
from app.services.DocumentExtractionService import DocumentExtractionService


def test_extract_transcript_loads_pdf_and_extracts_text(tmp_path: Path) -> None:
    content_id = "content-456"
    pdf_path = tmp_path / f"{content_id}.pdf"
    pdf_path.write_bytes(b"%PDF-1.4 placeholder")

    loader = LocalPdfLoader(storage_dir=tmp_path, fallback_path=None)
    extractor = MagicMock(spec=PdfExtractor)
    extractor.extract.return_value = "Extracted transcript text"
    service = DocumentExtractionService(
        pdf_extractor=extractor,
        pdf_loader=loader,
    )

    transcript = service.extract_transcript(content_id)

    assert transcript == "Extracted transcript text"
    extractor.extract.assert_called_once_with(b"%PDF-1.4 placeholder")


def test_extract_transcript_uses_fallback_pdf(tmp_path: Path) -> None:
    fallback = tmp_path / "fallback.pdf"
    fallback.write_bytes(b"fallback-pdf")

    loader = LocalPdfLoader(storage_dir=tmp_path / "missing", fallback_path=fallback)
    extractor = MagicMock(spec=PdfExtractor)
    extractor.extract.return_value = "Fallback transcript"
    service = DocumentExtractionService(
        pdf_extractor=extractor,
        pdf_loader=loader,
    )

    transcript = service.extract_transcript("missing-content")

    assert transcript == "Fallback transcript"
    extractor.extract.assert_called_once_with(b"fallback-pdf")


def test_extract_transcript_raises_when_pdf_missing(tmp_path: Path) -> None:
    service = DocumentExtractionService(
        pdf_extractor=PdfExtractor(),
        pdf_loader=LocalPdfLoader(storage_dir=tmp_path, fallback_path=None),
    )

    with pytest.raises(PdfNotFoundError):
        service.extract_transcript("missing-content")
