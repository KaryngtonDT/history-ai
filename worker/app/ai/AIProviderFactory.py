from app.ai.AIExecutionMode import AIExecutionMode
from app.ai.AIModel import AIModel
from app.ai.AIProviderInterface import AIProviderInterface
from app.ai.AIProviderType import AIProviderType
from app.ai.GeminiProvider import MODE_TO_GEMINI_MODEL, GeminiProvider
from app.ai.MockAIProvider import MODE_TO_MOCK_MODEL, MockAIProvider
from app.ai.UnsupportedAIProviderError import UnsupportedAIProviderError


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

        if resolved_provider is AIProviderType.GEMINI:
            model = AIProviderFactory.resolve_model(resolved_provider, mode)
            return GeminiProvider(model=model)

        msg = f"Provider {provider.value!r} is not implemented yet."
        raise UnsupportedAIProviderError(msg)

    @staticmethod
    def resolve_model(
        provider: AIProviderType,
        mode: AIExecutionMode,
    ) -> AIModel:
        if provider is AIProviderType.MOCK:
            return MODE_TO_MOCK_MODEL[mode]

        if provider is AIProviderType.GEMINI:
            return MODE_TO_GEMINI_MODEL[mode]

        msg = f"Provider {provider.value!r} is not implemented yet."
        raise UnsupportedAIProviderError(msg)
