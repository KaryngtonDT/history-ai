from app.ai.AIProviderInterface import AIProviderInterface
from app.generators.ArtifactGenerationRequest import ArtifactGenerationRequest
from app.generators.ArtifactGenerationResult import ArtifactGenerationResult
from app.generators.ArtifactGeneratorConfigurationError import (
    ArtifactGeneratorConfigurationError,
)
from app.generators.ArtifactGeneratorInterface import ArtifactGeneratorInterface
from app.generators.ArtifactType import ARTIFACT_TYPE_TIMELINE
from app.generators.TimelineGenerationError import TimelineGenerationError


class TimelineArtifactGenerator(ArtifactGeneratorInterface):
    TIMELINE_PROMPT_TEMPLATE = (
        "Generate a chronological timeline from the following transcript.\n"
        "Rules:\n"
        "- chronological order\n"
        "- concise\n"
        "- markdown output\n"
        "- use headings\n"
        "- use bullet points\n"
        "- include dates or periods when explicitly mentioned\n"
        "- do not invent dates\n"
        "- keep factual\n\n"
        "Use Markdown with this structure:\n\n"
        "# Timeline\n\n"
        "## <Period or era>\n\n"
        "- <date or period if mentioned> — <event>\n"
        "- <event>\n\n"
        "## <Next period>\n\n"
        "- <event>\n\n"
        "Transcript:\n\n{transcript}"
    )

    def __init__(self, ai_provider: AIProviderInterface) -> None:
        self._ai_provider = ai_provider

    def generate(
        self,
        request: ArtifactGenerationRequest,
    ) -> ArtifactGenerationResult:
        if request.artifact_type != ARTIFACT_TYPE_TIMELINE:
            msg = (
                f"Expected artifact type {ARTIFACT_TYPE_TIMELINE!r}, "
                f"got {request.artifact_type!r}."
            )
            raise ArtifactGeneratorConfigurationError(msg)

        normalized = request.transcript.strip()
        if not normalized:
            msg = "Transcript is empty; cannot generate timeline."
            raise TimelineGenerationError(msg)

        prompt = self.TIMELINE_PROMPT_TEMPLATE.format(transcript=normalized)
        response = self._ai_provider.generate_text(prompt)
        return ArtifactGenerationResult(
            artifact_type=ARTIFACT_TYPE_TIMELINE,
            content=response.text,
        )
