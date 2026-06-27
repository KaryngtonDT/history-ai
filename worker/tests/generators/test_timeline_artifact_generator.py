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
from app.generators.ArtifactGeneratorFactory import ArtifactGeneratorFactory
from app.generators.ArtifactGeneratorInterface import ArtifactGeneratorInterface
from app.generators.ArtifactType import ARTIFACT_TYPE_TIMELINE
from app.generators.TimelineArtifactGenerator import TimelineArtifactGenerator


def test_delegates_to_ai_provider_interface() -> None:
    ai_provider = MagicMock(spec=AIProviderInterface)
    ai_provider.generate_text.return_value = AIProviderResponse(
        text="# Timeline\n\n## Ancient Rome\n\n- 753 BC — Foundation of Rome",
        model="mock-balanced",
        provider="mock",
    )
    generator = TimelineArtifactGenerator(ai_provider=ai_provider)
    transcript = "Rome was founded in 753 BC."

    result = generator.generate(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_TIMELINE,
            transcript=transcript,
        ),
    )

    ai_provider.generate_text.assert_called_once()
    prompt = ai_provider.generate_text.call_args.args[0]
    assert "chronological timeline" in prompt
    assert "do not invent dates" in prompt
    assert transcript in prompt
    assert "# Timeline" in prompt
    assert result.artifact_type == ARTIFACT_TYPE_TIMELINE
    assert result.content.startswith("# Timeline")


def test_returns_mock_ai_provider_response() -> None:
    ai_provider = MockAIProvider(model=MOCK_BALANCED_MODEL)
    generator = TimelineArtifactGenerator(ai_provider=ai_provider)
    transcript = "Augustus became emperor."

    result = generator.generate(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_TIMELINE,
            transcript=transcript,
        ),
    )

    assert result.artifact_type == ARTIFACT_TYPE_TIMELINE
    assert result.content.startswith("Mock response:")
    assert transcript in result.content


def test_rejects_mismatched_artifact_type() -> None:
    ai_provider = MagicMock(spec=AIProviderInterface)
    generator = TimelineArtifactGenerator(ai_provider=ai_provider)

    with pytest.raises(
        ArtifactGeneratorConfigurationError,
        match="Expected artifact type 'timeline'",
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
    generator = TimelineArtifactGenerator(ai_provider=ai_provider)

    with pytest.raises(ArtifactGenerationRequestError, match="transcript"):
        generator.generate(
            ArtifactGenerationRequest(
                artifact_type=ARTIFACT_TYPE_TIMELINE,
                transcript="",
            ),
        )

    ai_provider.generate_text.assert_not_called()


def test_prompt_contains_timeline_instructions() -> None:
    ai_provider = MagicMock(spec=AIProviderInterface)
    ai_provider.generate_text.return_value = AIProviderResponse(
        text="# Timeline",
        model="mock-balanced",
        provider="mock",
    )
    generator = TimelineArtifactGenerator(ai_provider=ai_provider)

    generator.generate(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_TIMELINE,
            transcript="History content.",
        ),
    )

    prompt = ai_provider.generate_text.call_args.args[0]
    assert "chronological order" in prompt
    assert "markdown output" in prompt
    assert "use headings" in prompt
    assert "use bullet points" in prompt
    assert "keep factual" in prompt


def test_factory_creates_timeline_generator(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("AI_PROVIDER", "mock")
    monkeypatch.setenv("AI_EXECUTION_MODE", "balanced")

    generator = ArtifactGeneratorFactory.create(ARTIFACT_TYPE_TIMELINE)

    assert isinstance(generator, TimelineArtifactGenerator)
    assert isinstance(generator, ArtifactGeneratorInterface)


def test_factory_timeline_generator_respects_mock_ai_env(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("AI_PROVIDER", "mock")
    monkeypatch.setenv("AI_EXECUTION_MODE", "balanced")

    generator = ArtifactGeneratorFactory.create(ARTIFACT_TYPE_TIMELINE)
    result = generator.generate(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_TIMELINE,
            transcript="The Roman Empire was vast.",
        ),
    )

    assert isinstance(generator, TimelineArtifactGenerator)
    assert result.artifact_type == ARTIFACT_TYPE_TIMELINE
    assert result.content.startswith("Mock response:")
