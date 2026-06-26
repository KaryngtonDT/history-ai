from app.extractors.PdfExtractor import PdfExtractor
from app.loaders.LocalPdfLoader import LocalPdfLoader


class DocumentExtractionService:
    def __init__(
        self,
        pdf_extractor: PdfExtractor | None = None,
        pdf_loader: LocalPdfLoader | None = None,
    ) -> None:
        self._pdf_extractor = pdf_extractor or PdfExtractor()
        self._pdf_loader = pdf_loader or LocalPdfLoader.from_env()

    def extract_transcript(self, content_id: str) -> str:
        pdf_bytes = self._pdf_loader.load(content_id)
        return self._pdf_extractor.extract(pdf_bytes)
