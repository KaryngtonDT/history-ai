from app.ai.AIProviderInterface import AIProviderInterface
from app.ai.AIProviderResponse import AIProviderResponse


class AIProviderError(Exception):
    """Raised when text cannot be generated from a prompt."""


class MockAIProvider(AIProviderInterface):
    PROVIDER_NAME = "mock"
    MODEL_NAME = "mock-deterministic-v1"
    RESPONSE_PREFIX = "Mock response: "

    def generate_text(self, prompt: str) -> AIProviderResponse:
        normalized = prompt.strip()

        if not normalized:
            msg = "Prompt is empty."
            raise AIProviderError(msg)

        return AIProviderResponse(
            text=f"{self.RESPONSE_PREFIX}{normalized}",
            model=self.MODEL_NAME,
            provider=self.PROVIDER_NAME,
        )
