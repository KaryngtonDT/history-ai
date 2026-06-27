import pytest

from app.ai.AIExecutionMode import AIExecutionMode
from app.ai.AIProviderFactory import AIProviderFactory
from app.ai.AIProviderType import AIProviderType
from app.ai.GeminiProvider import (
    GEMINI_FLASH_MODEL,
    GEMINI_MODELS,
    GEMINI_PRO_MODEL,
    GeminiProvider,
)
from app.ai.NotImplementedAIProviderError import NotImplementedAIProviderError


def test_factory_returns_gemini_provider() -> None:
    provider = AIProviderFactory.create(
        provider=AIProviderType.GEMINI,
        mode=AIExecutionMode.BALANCED,
    )

    assert isinstance(provider, GeminiProvider)
    assert provider.model == GEMINI_FLASH_MODEL


def test_exposes_gemini_model_metadata() -> None:
    assert set(GEMINI_MODELS) == {"gemini-flash", "gemini-pro"}
    assert GEMINI_FLASH_MODEL.provider is AIProviderType.GEMINI
    assert GEMINI_PRO_MODEL.provider is AIProviderType.GEMINI
    assert GEMINI_FLASH_MODEL.supports_streaming is True
    assert GEMINI_PRO_MODEL.max_context == 2_000_000


@pytest.mark.parametrize(
    ("mode", "expected_model_name"),
    [
        (AIExecutionMode.FAST, "gemini-flash"),
        (AIExecutionMode.BALANCED, "gemini-flash"),
        (AIExecutionMode.QUALITY, "gemini-pro"),
        (AIExecutionMode.CHEAP, "gemini-flash"),
        (AIExecutionMode.LOCAL, "gemini-flash"),
    ],
)
def test_factory_selects_gemini_model_by_mode(
    mode: AIExecutionMode,
    expected_model_name: str,
) -> None:
    provider = AIProviderFactory.create(
        provider=AIProviderType.GEMINI,
        mode=mode,
    )

    assert provider.model.model_name == expected_model_name


def test_generate_text_raises_not_implemented_error() -> None:
    provider = AIProviderFactory.create(
        provider=AIProviderType.GEMINI,
        mode=AIExecutionMode.BALANCED,
    )

    with pytest.raises(NotImplementedAIProviderError, match="not implemented yet"):
        provider.generate_text("Summarize the Roman Empire.")
