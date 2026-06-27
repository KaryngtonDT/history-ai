from app.ai.AIExecutionMode import AIExecutionMode
from app.ai.AIModel import AIModel
from app.ai.AIProviderInterface import AIProviderInterface
from app.ai.AIProviderType import AIProviderType
from app.ai.MockAIProvider import MODE_TO_MOCK_MODEL, MockAIProvider


class UnsupportedAIProviderError(Exception):
    """Raised when a provider type is not implemented yet."""


class AIProviderFactory:
    @staticmethod
    def create(
        provider: AIProviderType = AIProviderType.MOCK,
        mode: AIExecutionMode = AIExecutionMode.BALANCED,
    ) -> AIProviderInterface:
        resolved_provider = (
            AIProviderType.MOCK if provider is AIProviderType.AUTO else provider
        )

        if resolved_provider is AIProviderType.MOCK:
            model = AIProviderFactory.resolve_model(resolved_provider, mode)
            return MockAIProvider(model=model)

        msg = f"Provider {provider.value!r} is not implemented yet."
        raise UnsupportedAIProviderError(msg)

    @staticmethod
    def resolve_model(
        provider: AIProviderType,
        mode: AIExecutionMode,
    ) -> AIModel:
        if provider is AIProviderType.MOCK:
            return MODE_TO_MOCK_MODEL[mode]

        msg = f"Provider {provider.value!r} is not implemented yet."
        raise UnsupportedAIProviderError(msg)
