from app.generators.ArtifactGenerationRequest import ArtifactGenerationRequest
from app.generators.ArtifactGenerationResult import ArtifactGenerationResult
from app.generators.ArtifactGeneratorConfigurationError import (
    ArtifactGeneratorConfigurationError,
)
from app.generators.ArtifactGeneratorInterface import ArtifactGeneratorInterface
from app.generators.ArtifactType import ARTIFACT_TYPE_SUMMARY
from app.generators.SummaryGeneratorInterface import SummaryGeneratorInterface


class SummaryArtifactGenerator(ArtifactGeneratorInterface):
    """Adapts SummaryGeneratorInterface to the generic artifact contract."""

    def __init__(self, summary_generator: SummaryGeneratorInterface) -> None:
        self._summary_generator = summary_generator

    def generate(
        self,
        request: ArtifactGenerationRequest,
    ) -> ArtifactGenerationResult:
        if request.artifact_type != ARTIFACT_TYPE_SUMMARY:
            msg = (
                f"Expected artifact type {ARTIFACT_TYPE_SUMMARY!r}, "
                f"got {request.artifact_type!r}."
            )
            raise ArtifactGeneratorConfigurationError(msg)

        content = self._summary_generator.generate(request.transcript)
        return ArtifactGenerationResult(
            artifact_type=ARTIFACT_TYPE_SUMMARY,
            content=content,
        )
