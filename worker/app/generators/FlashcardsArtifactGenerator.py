from app.ai.AIProviderInterface import AIProviderInterface
from app.generators.ArtifactGenerationRequest import ArtifactGenerationRequest
from app.generators.ArtifactGenerationResult import ArtifactGenerationResult
from app.generators.ArtifactGeneratorConfigurationError import (
    ArtifactGeneratorConfigurationError,
)
from app.generators.ArtifactGeneratorInterface import ArtifactGeneratorInterface
from app.generators.ArtifactType import ARTIFACT_TYPE_FLASHCARDS
from app.generators.FlashcardsGenerationError import FlashcardsGenerationError

DEFAULT_CARD_COUNT = 10


class FlashcardsArtifactGenerator(ArtifactGeneratorInterface):
    FLASHCARDS_PROMPT_TEMPLATE = (
        "Generate exactly {card_count} study flashcards based on the following "
        "transcript.\n"
        "Use Markdown with this structure:\n\n"
        "# Flashcards\n\n"
        "## Card 1\n\n"
        "Front:\n"
        "<question or term>\n\n"
        "Back:\n"
        "<answer or definition>\n\n"
        "---\n\n"
        "## Card 2\n\n"
        "Front:\n"
        "...\n\n"
        "Back:\n"
        "...\n\n"
        "Repeat for each card.\n\n"
        "Transcript:\n\n{transcript}"
    )

    def __init__(
        self,
        ai_provider: AIProviderInterface,
        card_count: int = DEFAULT_CARD_COUNT,
    ) -> None:
        self._ai_provider = ai_provider
        self._card_count = card_count

    def generate(
        self,
        request: ArtifactGenerationRequest,
    ) -> ArtifactGenerationResult:
        if request.artifact_type != ARTIFACT_TYPE_FLASHCARDS:
            msg = (
                f"Expected artifact type {ARTIFACT_TYPE_FLASHCARDS!r}, "
                f"got {request.artifact_type!r}."
            )
            raise ArtifactGeneratorConfigurationError(msg)

        normalized = request.transcript.strip()
        if not normalized:
            msg = "Transcript is empty; cannot generate flashcards."
            raise FlashcardsGenerationError(msg)

        prompt = self.FLASHCARDS_PROMPT_TEMPLATE.format(
            card_count=self._card_count,
            transcript=normalized,
        )
        response = self._ai_provider.generate_text(prompt)
        return ArtifactGenerationResult(
            artifact_type=ARTIFACT_TYPE_FLASHCARDS,
            content=response.text,
        )
