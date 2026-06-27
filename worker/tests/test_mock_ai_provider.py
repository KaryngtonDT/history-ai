import pytest

from app.ai.AIProviderResponse import AIProviderResponse
from app.ai.MockAIProvider import AIProviderError, MockAIProvider


def test_returns_deterministic_response() -> None:
    provider = MockAIProvider()
    prompt = "Summarize the Roman Empire."

    first = provider.generate_text(prompt)
    second = provider.generate_text(prompt)

    assert first == second
    assert first == AIProviderResponse(
        text="Mock response: Summarize the Roman Empire.",
        model="mock-deterministic-v1",
        provider="mock",
    )


def test_rejects_empty_prompt() -> None:
    provider = MockAIProvider()

    with pytest.raises(AIProviderError, match="Prompt is empty"):
        provider.generate_text("")


def test_rejects_whitespace_only_prompt() -> None:
    provider = MockAIProvider()

    with pytest.raises(AIProviderError, match="Prompt is empty"):
        provider.generate_text("   \n\t  ")
