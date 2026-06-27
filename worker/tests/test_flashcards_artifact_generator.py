from unittest.mock import MagicMock

import pytest

from app.ai.AIProviderInterface import AIProviderInterface
from app.ai.AIProviderResponse import AIProviderResponse
from app.ai.MockAIProvider import MOCK_BALANCED_MODEL, MockAIProvider
from app.generators.ArtifactGenerationRequest import ArtifactGenerationRequest
from app.generators.ArtifactGenerationRequestError import ArtifactGenerationRequestError
from app.generators.ArtifactGeneratorConfigurationError import (
    ArtifactGeneratorConfigurationError,
)
from app.generators.ArtifactType import ARTIFACT_TYPE_FLASHCARDS
from app.generators.FlashcardsArtifactGenerator import (
    DEFAULT_CARD_COUNT,
    FlashcardsArtifactGenerator,
)


def test_delegates_to_ai_provider_interface() -> None:
    ai_provider = MagicMock(spec=AIProviderInterface)
    ai_provider.generate_text.return_value = AIProviderResponse(
        text="# Flashcards\n\n## Card 1\n\nFront:\nTerm\n\nBack:\nDefinition",
        model="mock-balanced",
        provider="mock",
    )
    generator = FlashcardsArtifactGenerator(ai_provider=ai_provider)
    transcript = "The Roman Empire was vast."

    result = generator.generate(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_FLASHCARDS,
            transcript=transcript,
        ),
    )

    ai_provider.generate_text.assert_called_once()
    prompt = ai_provider.generate_text.call_args.args[0]
    assert str(DEFAULT_CARD_COUNT) in prompt
    assert transcript in prompt
    assert "# Flashcards" in prompt
    assert "Front:" in prompt
    assert "Back:" in prompt
    assert result.artifact_type == ARTIFACT_TYPE_FLASHCARDS
    assert result.content.startswith("# Flashcards")


def test_returns_mock_ai_provider_response() -> None:
    ai_provider = MockAIProvider(model=MOCK_BALANCED_MODEL)
    generator = FlashcardsArtifactGenerator(ai_provider=ai_provider)
    transcript = "The Roman Empire was vast."

    result = generator.generate(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_FLASHCARDS,
            transcript=transcript,
        ),
    )

    assert result.artifact_type == ARTIFACT_TYPE_FLASHCARDS
    assert result.content.startswith("Mock response:")
    assert transcript in result.content


def test_rejects_mismatched_artifact_type() -> None:
    ai_provider = MagicMock(spec=AIProviderInterface)
    generator = FlashcardsArtifactGenerator(ai_provider=ai_provider)

    with pytest.raises(
        ArtifactGeneratorConfigurationError,
        match="Expected artifact type 'flashcards'",
    ):
        generator.generate(
            ArtifactGenerationRequest(
                artifact_type="quiz",
                transcript="Some transcript.",
            ),
        )

    ai_provider.generate_text.assert_not_called()


def test_rejects_empty_transcript_at_request_level() -> None:
    ai_provider = MagicMock(spec=AIProviderInterface)
    generator = FlashcardsArtifactGenerator(ai_provider=ai_provider)

    with pytest.raises(ArtifactGenerationRequestError, match="transcript"):
        generator.generate(
            ArtifactGenerationRequest(
                artifact_type=ARTIFACT_TYPE_FLASHCARDS,
                transcript="",
            ),
        )

    ai_provider.generate_text.assert_not_called()


def test_rejects_whitespace_only_transcript() -> None:
    ai_provider = MagicMock(spec=AIProviderInterface)
    generator = FlashcardsArtifactGenerator(ai_provider=ai_provider)

    with pytest.raises(ArtifactGenerationRequestError, match="transcript"):
        generator.generate(
            ArtifactGenerationRequest(
                artifact_type=ARTIFACT_TYPE_FLASHCARDS,
                transcript="   ",
            ),
        )

    ai_provider.generate_text.assert_not_called()


def test_prompt_requests_ten_flashcards() -> None:
    ai_provider = MagicMock(spec=AIProviderInterface)
    ai_provider.generate_text.return_value = AIProviderResponse(
        text="# Flashcards",
        model="mock-balanced",
        provider="mock",
    )
    generator = FlashcardsArtifactGenerator(ai_provider=ai_provider, card_count=10)

    generator.generate(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_FLASHCARDS,
            transcript="History content.",
        ),
    )

    prompt = ai_provider.generate_text.call_args.args[0]
    assert "10 study flashcards" in prompt
    assert "## Card 1" in prompt
