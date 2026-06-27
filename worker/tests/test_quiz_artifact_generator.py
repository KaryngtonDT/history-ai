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
from app.generators.ArtifactType import ARTIFACT_TYPE_QUIZ
from app.generators.QuizArtifactGenerator import (
    DEFAULT_QUESTION_COUNT,
    QuizArtifactGenerator,
)


def test_delegates_to_ai_provider_interface() -> None:
    ai_provider = MagicMock(spec=AIProviderInterface)
    ai_provider.generate_text.return_value = AIProviderResponse(
        text="# Quiz\n\n## Question 1\nGenerated quiz content.",
        model="mock-balanced",
        provider="mock",
    )
    generator = QuizArtifactGenerator(ai_provider=ai_provider)
    transcript = "The Roman Empire was vast."

    result = generator.generate(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_QUIZ,
            transcript=transcript,
        ),
    )

    ai_provider.generate_text.assert_called_once()
    prompt = ai_provider.generate_text.call_args.args[0]
    assert str(DEFAULT_QUESTION_COUNT) in prompt
    assert transcript in prompt
    assert "# Quiz" in prompt
    assert result.artifact_type == ARTIFACT_TYPE_QUIZ
    assert result.content == "# Quiz\n\n## Question 1\nGenerated quiz content."


def test_returns_mock_ai_provider_response() -> None:
    ai_provider = MockAIProvider(model=MOCK_BALANCED_MODEL)
    generator = QuizArtifactGenerator(ai_provider=ai_provider)
    transcript = "The Roman Empire was vast."

    result = generator.generate(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_QUIZ,
            transcript=transcript,
        ),
    )

    assert result.artifact_type == ARTIFACT_TYPE_QUIZ
    assert result.content.startswith("Mock response:")
    assert transcript in result.content


def test_rejects_mismatched_artifact_type() -> None:
    ai_provider = MagicMock(spec=AIProviderInterface)
    generator = QuizArtifactGenerator(ai_provider=ai_provider)

    with pytest.raises(
        ArtifactGeneratorConfigurationError,
        match="Expected artifact type 'quiz'",
    ):
        generator.generate(
            ArtifactGenerationRequest(
                artifact_type="summary",
                transcript="Some transcript.",
            ),
        )

    ai_provider.generate_text.assert_not_called()


def test_rejects_empty_transcript_at_request_level() -> None:
    ai_provider = MagicMock(spec=AIProviderInterface)
    generator = QuizArtifactGenerator(ai_provider=ai_provider)

    with pytest.raises(ArtifactGenerationRequestError, match="transcript"):
        generator.generate(
            ArtifactGenerationRequest(
                artifact_type=ARTIFACT_TYPE_QUIZ,
                transcript="",
            ),
        )

    ai_provider.generate_text.assert_not_called()


def test_prompt_requests_five_multiple_choice_questions() -> None:
    ai_provider = MagicMock(spec=AIProviderInterface)
    ai_provider.generate_text.return_value = AIProviderResponse(
        text="# Quiz",
        model="mock-balanced",
        provider="mock",
    )
    generator = QuizArtifactGenerator(ai_provider=ai_provider, question_count=5)

    generator.generate(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_QUIZ,
            transcript="History content.",
        ),
    )

    prompt = ai_provider.generate_text.call_args.args[0]
    assert "5 multiple-choice questions" in prompt
    assert "Answer:" in prompt
