from abc import ABC, abstractmethod

from app.ai.AIModel import AIModel
from app.ai.AIProviderResponse import AIProviderResponse


class AIProviderInterface(ABC):
    @property
    @abstractmethod
    def model(self) -> AIModel:
        """Selected model used by this provider instance."""

    @abstractmethod
    def generate_text(self, prompt: str) -> AIProviderResponse:
        """Generate text from the given prompt."""
