from abc import ABC, abstractmethod

from app.generators.ArtifactGenerationRequest import ArtifactGenerationRequest
from app.generators.ArtifactGenerationResult import ArtifactGenerationResult


class ArtifactGeneratorInterface(ABC):
    @abstractmethod
    def generate(
        self,
        request: ArtifactGenerationRequest,
    ) -> ArtifactGenerationResult:
        """Generate an artifact from the given request."""
