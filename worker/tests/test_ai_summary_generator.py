from unittest.mock import MagicMock

import pytest

from app.ai.AIProviderInterface import AIProviderInterface
from app.ai.AIProviderResponse import AIProviderResponse
from app.ai.MockAIProvider import MOCK_BALANCED_MODEL, MockAIProvider
from app.generators.AISummaryGenerator import AISummaryGenerator
from app.generators.DeterministicSummaryGenerator import SummaryGenerationError


def test_calls_ai_provider_interface() -> None:
    ai_provider = MagicMock(spec=AIProviderInterface)
    ai_provider.generate_text.return_value = AIProviderResponse(
        text="AI generated summary.",
        model="mock-balanced",
        provider="mock",
    )
    generator = AISummaryGenerator(ai_provider=ai_provider)
    transcript = "The Roman Empire was vast."

    summary = generator.generate(transcript)

    ai_provider.generate_text.assert_called_once_with(
        "Summarize the following transcript in a concise paragraph:\n\n"
        "The Roman Empire was vast.",
    )
    assert summary == "AI generated summary."


def test_returns_provider_response_text_with_mock_ai_provider() -> None:
    ai_provider = MockAIProvider(model=MOCK_BALANCED_MODEL)
    generator = AISummaryGenerator(ai_provider=ai_provider)
    transcript = "The Roman Empire was vast."

    summary = generator.generate(transcript)

    assert summary == (
        "Mock response: Summarize the following transcript in a concise "
        "paragraph:\n\nThe Roman Empire was vast."
    )


def test_rejects_empty_transcript() -> None:
    ai_provider = MockAIProvider(model=MOCK_BALANCED_MODEL)
    generator = AISummaryGenerator(ai_provider=ai_provider)

    with pytest.raises(SummaryGenerationError, match="Transcript is empty"):
        generator.generate("")


def test_rejects_whitespace_only_transcript() -> None:
    ai_provider = MockAIProvider(model=MOCK_BALANCED_MODEL)
    generator = AISummaryGenerator(ai_provider=ai_provider)

    with pytest.raises(SummaryGenerationError, match="Transcript is empty"):
        generator.generate("   \n\t  ")
