from abc import ABC, abstractmethod

from app.ai.AIProviderResponse import AIProviderResponse


class AIProviderInterface(ABC):
    @abstractmethod
    def generate_text(self, prompt: str) -> AIProviderResponse:
        """Generate text from the given prompt."""
