from io import BytesIO

from pypdf import PdfReader


class PdfExtractionError(Exception):
    """Raised when text cannot be extracted from a PDF."""


class PdfExtractor:
    def extract(self, pdf_bytes: bytes) -> str:
        if not pdf_bytes:
            raise PdfExtractionError("PDF payload is empty.")

        reader = PdfReader(BytesIO(pdf_bytes))
        parts: list[str] = []

        for page in reader.pages:
            text = page.extract_text()
            if text:
                parts.append(text.strip())

        transcript = "\n\n".join(part for part in parts if part).strip()

        if not transcript:
            raise PdfExtractionError("No extractable text found in PDF.")

        return transcript
