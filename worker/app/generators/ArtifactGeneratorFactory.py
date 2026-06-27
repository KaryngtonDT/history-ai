from app.ai.AIProviderFactory import AIProviderFactory
from app.generators.ArtifactGeneratorConfigurationError import (
    ArtifactGeneratorConfigurationError,
)
from app.generators.ArtifactGeneratorInterface import ArtifactGeneratorInterface
from app.generators.ArtifactType import (
    ARTIFACT_TYPE_QUIZ,
    ARTIFACT_TYPE_SUMMARY,
    SUPPORTED_ARTIFACT_TYPES,
)
from app.generators.QuizArtifactGenerator import QuizArtifactGenerator
from app.generators.SummaryArtifactGenerator import SummaryArtifactGenerator
from app.generators.SummaryGeneratorFactory import SummaryGeneratorFactory


class ArtifactGeneratorFactory:
    @staticmethod
    def create(artifact_type: str) -> ArtifactGeneratorInterface:
        normalized = artifact_type.strip().lower()

        if normalized == ARTIFACT_TYPE_SUMMARY:
            return SummaryArtifactGenerator(SummaryGeneratorFactory.create())

        if normalized == ARTIFACT_TYPE_QUIZ:
            ai_provider = AIProviderFactory.create_from_env()
            return QuizArtifactGenerator(ai_provider=ai_provider)

        if normalized in SUPPORTED_ARTIFACT_TYPES:
            msg = f"Artifact type {artifact_type!r} is not implemented yet."
            raise ArtifactGeneratorConfigurationError(msg)

        msg = (
            f"Unsupported artifact type {artifact_type!r}; "
            f"supported types: {sorted(SUPPORTED_ARTIFACT_TYPES)!r}."
        )
        raise ArtifactGeneratorConfigurationError(msg)
