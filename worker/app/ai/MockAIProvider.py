from app.ai.AIExecutionMode import AIExecutionMode
from app.ai.AIModel import AIModel
from app.ai.AIProviderInterface import AIProviderInterface
from app.ai.AIProviderResponse import AIProviderResponse
from app.ai.AIProviderType import AIProviderType

MOCK_FAST_MODEL = AIModel(
    provider=AIProviderType.MOCK,
    model_name="mock-fast",
    supports_streaming=True,
    supports_json=True,
    max_context=128_000,
)

MOCK_BALANCED_MODEL = AIModel(
    provider=AIProviderType.MOCK,
    model_name="mock-balanced",
    supports_streaming=True,
    supports_json=True,
    max_context=256_000,
)

MOCK_QUALITY_MODEL = AIModel(
    provider=AIProviderType.MOCK,
    model_name="mock-quality",
    supports_streaming=True,
    supports_json=False,
    max_context=512_000,
)

MOCK_MODELS: dict[str, AIModel] = {
    MOCK_FAST_MODEL.model_name: MOCK_FAST_MODEL,
    MOCK_BALANCED_MODEL.model_name: MOCK_BALANCED_MODEL,
    MOCK_QUALITY_MODEL.model_name: MOCK_QUALITY_MODEL,
}

MODE_TO_MOCK_MODEL: dict[AIExecutionMode, AIModel] = {
    AIExecutionMode.FAST: MOCK_FAST_MODEL,
    AIExecutionMode.BALANCED: MOCK_BALANCED_MODEL,
    AIExecutionMode.QUALITY: MOCK_QUALITY_MODEL,
    AIExecutionMode.CHEAP: MOCK_FAST_MODEL,
    AIExecutionMode.LOCAL: MOCK_BALANCED_MODEL,
}


class AIProviderError(Exception):
    """Raised when text cannot be generated from a prompt."""


class MockAIProvider(AIProviderInterface):
    RESPONSE_PREFIX = "Mock response: "

    def __init__(self, model: AIModel) -> None:
        if model.provider is not AIProviderType.MOCK:
            msg = "MockAIProvider requires a mock AIModel."
            raise ValueError(msg)
        self._model = model

    @property
    def model(self) -> AIModel:
        return self._model

    def generate_text(self, prompt: str) -> AIProviderResponse:
        normalized = prompt.strip()

        if not normalized:
            msg = "Prompt is empty."
            raise AIProviderError(msg)

        return AIProviderResponse(
            text=f"{self.RESPONSE_PREFIX}{normalized}",
            model=self._model.model_name,
            provider=self._model.provider.value,
        )
