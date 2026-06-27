import pytest

from app.ai.AIModel import AIModel
from app.ai.AIProviderResponse import AIProviderResponse
from app.ai.MockAIProvider import (
    MOCK_BALANCED_MODEL,
    MOCK_FAST_MODEL,
    MOCK_MODELS,
    MOCK_QUALITY_MODEL,
    AIProviderError,
    MockAIProvider,
)


@pytest.mark.parametrize(
    ("model", "expected_model_name"),
    [
        (MOCK_FAST_MODEL, "mock-fast"),
        (MOCK_BALANCED_MODEL, "mock-balanced"),
        (MOCK_QUALITY_MODEL, "mock-quality"),
    ],
)
def test_exposes_mock_models_with_metadata(
    model: AIModel,
    expected_model_name: str,
) -> None:
    provider = MockAIProvider(model=model)

    assert provider.model.model_name == expected_model_name
    assert provider.model.provider.value == "mock"
    assert provider.model.supports_streaming is True
    assert provider.model.max_context > 0


def test_mock_model_catalog_contains_expected_models() -> None:
    assert set(MOCK_MODELS) == {"mock-fast", "mock-balanced", "mock-quality"}


def test_returns_deterministic_response() -> None:
    provider = MockAIProvider(model=MOCK_BALANCED_MODEL)
    prompt = "Summarize the Roman Empire."

    first = provider.generate_text(prompt)
    second = provider.generate_text(prompt)

    assert first == second
    assert first == AIProviderResponse(
        text="Mock response: Summarize the Roman Empire.",
        model="mock-balanced",
        provider="mock",
    )


def test_rejects_empty_prompt() -> None:
    provider = MockAIProvider(model=MOCK_BALANCED_MODEL)

    with pytest.raises(AIProviderError, match="Prompt is empty"):
        provider.generate_text("")


def test_rejects_whitespace_only_prompt() -> None:
    provider = MockAIProvider(model=MOCK_BALANCED_MODEL)

    with pytest.raises(AIProviderError, match="Prompt is empty"):
        provider.generate_text("   \n\t  ")
