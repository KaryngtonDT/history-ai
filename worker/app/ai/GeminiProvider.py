from app.ai.AIExecutionMode import AIExecutionMode
from app.ai.AIModel import AIModel
from app.ai.AIProviderConfigurationError import AIProviderConfigurationError
from app.ai.AIProviderInterface import AIProviderInterface
from app.ai.AIProviderResponse import AIProviderResponse
from app.ai.AIProviderType import AIProviderType
from app.ai.NotImplementedAIProviderError import NotImplementedAIProviderError

GEMINI_FLASH_MODEL = AIModel(
    provider=AIProviderType.GEMINI,
    model_name="gemini-flash",
    supports_streaming=True,
    supports_json=True,
    max_context=1_000_000,
)

GEMINI_PRO_MODEL = AIModel(
    provider=AIProviderType.GEMINI,
    model_name="gemini-pro",
    supports_streaming=True,
    supports_json=True,
    max_context=2_000_000,
)

GEMINI_MODELS: dict[str, AIModel] = {
    GEMINI_FLASH_MODEL.model_name: GEMINI_FLASH_MODEL,
    GEMINI_PRO_MODEL.model_name: GEMINI_PRO_MODEL,
}

MODE_TO_GEMINI_MODEL: dict[AIExecutionMode, AIModel] = {
    AIExecutionMode.FAST: GEMINI_FLASH_MODEL,
    AIExecutionMode.BALANCED: GEMINI_FLASH_MODEL,
    AIExecutionMode.QUALITY: GEMINI_PRO_MODEL,
    AIExecutionMode.CHEAP: GEMINI_FLASH_MODEL,
    AIExecutionMode.LOCAL: GEMINI_FLASH_MODEL,
}


class GeminiProvider(AIProviderInterface):
    NOT_IMPLEMENTED_MESSAGE = (
        "GeminiProvider.generate_text() is not implemented yet; "
        "no external API call is performed."
    )

    def __init__(self, model: AIModel) -> None:
        if model.provider is not AIProviderType.GEMINI:
            msg = "GeminiProvider requires a Gemini AIModel."
            raise AIProviderConfigurationError(msg)
        self._model = model

    @property
    def model(self) -> AIModel:
        return self._model

    def generate_text(self, prompt: str) -> AIProviderResponse:
        _ = prompt
        raise NotImplementedAIProviderError(self.NOT_IMPLEMENTED_MESSAGE)
