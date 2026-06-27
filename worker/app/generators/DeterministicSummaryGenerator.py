import re

from app.generators.SummaryGenerationError import SummaryGenerationError
from app.generators.SummaryGeneratorInterface import SummaryGeneratorInterface

SENTENCE_PATTERN = re.compile(r"[^.!?]+[.!?]+|[^.!?]+$")


class DeterministicSummaryGenerator(SummaryGeneratorInterface):
    DEFAULT_MAX_SENTENCES = 3

    def __init__(self, max_sentences: int = DEFAULT_MAX_SENTENCES) -> None:
        self._max_sentences = max_sentences

    def generate(self, transcript: str) -> str:
        sentences = self._split_meaningful_sentences(transcript)

        if not sentences:
            msg = "Transcript is empty; cannot generate summary."
            raise SummaryGenerationError(msg)

        selected = sentences[: self._max_sentences]
        return " ".join(selected)

    def _split_meaningful_sentences(self, transcript: str) -> list[str]:
        normalized = transcript.strip()

        if not normalized:
            return []

        return [
            sentence.strip()
            for sentence in SENTENCE_PATTERN.findall(normalized)
            if sentence.strip()
        ]
