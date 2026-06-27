import os

from app.ai.AIExecutionMode import AIExecutionMode
from app.ai.AIModel import AIModel
from app.ai.AIProviderConfigurationError import AIProviderConfigurationError
from app.ai.AIProviderInterface import AIProviderInterface
from app.ai.AIProviderType import AIProviderType
from app.ai.GeminiProvider import MODE_TO_GEMINI_MODEL, GeminiProvider
from app.ai.MockAIProvider import MODE_TO_MOCK_MODEL, MockAIProvider
from app.ai.UnsupportedAIProviderError import UnsupportedAIProviderError

DEFAULT_AI_PROVIDER = AIProviderType.MOCK
DEFAULT_AI_EXECUTION_MODE = AIExecutionMode.BALANCED


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
    def create_from_env() -> AIProviderInterface:
        provider = AIProviderFactory.parse_provider_from_env(
            os.environ.get("AI_PROVIDER"),
        )
        mode = AIProviderFactory.parse_execution_mode_from_env(
            os.environ.get("AI_EXECUTION_MODE"),
        )

        if provider is AIProviderType.GEMINI and not os.environ.get(
            "GEMINI_API_KEY",
            "",
        ).strip():
            msg = "GEMINI_API_KEY is not configured."
            raise AIProviderConfigurationError(msg)

        return AIProviderFactory.create(provider=provider, mode=mode)

    @staticmethod
    def parse_provider_from_env(value: str | None) -> AIProviderType:
        normalized = (value or DEFAULT_AI_PROVIDER.value).strip().lower()

        try:
            return AIProviderType(normalized)
        except ValueError as exc:
            msg = f"Invalid AI_PROVIDER {normalized!r}."
            raise AIProviderConfigurationError(msg) from exc

    @staticmethod
    def parse_execution_mode_from_env(value: str | None) -> AIExecutionMode:
        normalized = (value or DEFAULT_AI_EXECUTION_MODE.value).strip().lower()

        try:
            return AIExecutionMode(normalized)
        except ValueError as exc:
            msg = f"Invalid AI_EXECUTION_MODE {normalized!r}."
            raise AIProviderConfigurationError(msg) from exc

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
