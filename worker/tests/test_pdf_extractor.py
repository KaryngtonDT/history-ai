from io import BytesIO
from pathlib import Path

import pytest
from pypdf import PdfWriter
from pypdf.generic import (
    ArrayObject,
    DecodedStreamObject,
    DictionaryObject,
    NameObject,
    NumberObject,
)

from app.extractors.PdfExtractor import PdfExtractionError, PdfExtractor


def _build_pdf(text: str) -> bytes:
    buffer = BytesIO()
    writer = PdfWriter()
    page = writer.add_blank_page(width=612, height=792)

    stream = DecodedStreamObject()
    stream.set_data(f"BT /F1 24 Tf 72 720 Td ({text}) Tj ET".encode())
    page[NameObject("/Contents")] = stream
    page[NameObject("/Resources")] = DictionaryObject(
        {
            NameObject("/Font"): DictionaryObject(
                {
                    NameObject("/F1"): DictionaryObject(
                        {
                            NameObject("/Type"): NameObject("/Font"),
                            NameObject("/Subtype"): NameObject("/Type1"),
                            NameObject("/BaseFont"): NameObject("/Helvetica"),
                        },
                    ),
                },
            ),
        },
    )
    page[NameObject("/MediaBox")] = ArrayObject(
        [NumberObject(0), NumberObject(0), NumberObject(612), NumberObject(792)],
    )

    writer.write(buffer)
    return buffer.getvalue()


def test_extracts_text_from_pdf_bytes() -> None:
    extractor = PdfExtractor()
    pdf_bytes = _build_pdf("The Roman Empire was a vast civilization.")

    transcript = extractor.extract(pdf_bytes)

    assert "Roman Empire" in transcript


def test_raises_when_pdf_has_no_text() -> None:
    extractor = PdfExtractor()
    buffer = BytesIO()
    writer = PdfWriter()
    writer.add_blank_page(width=612, height=792)
    writer.write(buffer)

    with pytest.raises(PdfExtractionError):
        extractor.extract(buffer.getvalue())


def test_extracts_text_from_fixture_pdf() -> None:
    fixture_path = Path(__file__).resolve().parents[1] / "fixtures" / "sample.pdf"
    extractor = PdfExtractor()

    transcript = extractor.extract(fixture_path.read_bytes())

    assert "Roman Empire" in transcript
