import pytest

from app.ai.AIProviderConfigurationError import AIProviderConfigurationError
from app.generators.ArtifactGenerationRequest import ArtifactGenerationRequest
from app.generators.ArtifactGeneratorConfigurationError import (
    ArtifactGeneratorConfigurationError,
)
from app.generators.ArtifactGeneratorFactory import ArtifactGeneratorFactory
from app.generators.ArtifactGeneratorInterface import ArtifactGeneratorInterface
from app.generators.ArtifactType import ARTIFACT_TYPE_QUIZ, ARTIFACT_TYPE_SUMMARY
from app.generators.QuizArtifactGenerator import QuizArtifactGenerator
from app.generators.SummaryArtifactGenerator import SummaryArtifactGenerator


def test_create_returns_summary_artifact_generator(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("SUMMARY_GENERATOR", "deterministic")

    generator = ArtifactGeneratorFactory.create(ARTIFACT_TYPE_SUMMARY)

    assert isinstance(generator, SummaryArtifactGenerator)
    assert isinstance(generator, ArtifactGeneratorInterface)


def test_create_summary_artifact_generator_respects_ai_env(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("SUMMARY_GENERATOR", "ai")
    monkeypatch.setenv("AI_PROVIDER", "mock")
    monkeypatch.setenv("AI_EXECUTION_MODE", "balanced")

    generator = ArtifactGeneratorFactory.create(ARTIFACT_TYPE_SUMMARY)
    result = generator.generate(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_SUMMARY,
            transcript="Roman Empire.",
        ),
    )

    assert isinstance(generator, SummaryArtifactGenerator)
    assert result.artifact_type == ARTIFACT_TYPE_SUMMARY
    assert result.content.startswith("Mock response:")


def test_create_returns_quiz_artifact_generator(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("AI_PROVIDER", "mock")
    monkeypatch.setenv("AI_EXECUTION_MODE", "balanced")

    generator = ArtifactGeneratorFactory.create(ARTIFACT_TYPE_QUIZ)

    assert isinstance(generator, QuizArtifactGenerator)
    assert isinstance(generator, ArtifactGeneratorInterface)


def test_create_quiz_artifact_generator_respects_mock_ai_env(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("AI_PROVIDER", "mock")
    monkeypatch.setenv("AI_EXECUTION_MODE", "balanced")

    generator = ArtifactGeneratorFactory.create(ARTIFACT_TYPE_QUIZ)
    result = generator.generate(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_QUIZ,
            transcript="The Roman Empire was vast.",
        ),
    )

    assert isinstance(generator, QuizArtifactGenerator)
    assert result.artifact_type == ARTIFACT_TYPE_QUIZ
    assert result.content.startswith("Mock response:")


def test_create_rejects_unsupported_artifact_type() -> None:
    with pytest.raises(
        ArtifactGeneratorConfigurationError,
        match="Unsupported artifact type",
    ):
        ArtifactGeneratorFactory.create("unknown")


def test_create_rejects_future_artifact_type_not_yet_implemented() -> None:
    with pytest.raises(
        ArtifactGeneratorConfigurationError,
        match="Unsupported artifact type",
    ):
        ArtifactGeneratorFactory.create("flashcards")


def test_create_summary_without_gemini_key_fails_clearly(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("SUMMARY_GENERATOR", "ai")
    monkeypatch.setenv("AI_PROVIDER", "gemini")
    monkeypatch.setenv("AI_EXECUTION_MODE", "balanced")
    monkeypatch.setenv("GEMINI_API_KEY", "")

    with pytest.raises(AIProviderConfigurationError, match="GEMINI_API_KEY"):
        ArtifactGeneratorFactory.create(ARTIFACT_TYPE_SUMMARY)


def test_create_quiz_without_gemini_key_fails_clearly(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("AI_PROVIDER", "gemini")
    monkeypatch.setenv("AI_EXECUTION_MODE", "balanced")
    monkeypatch.setenv("GEMINI_API_KEY", "")

    with pytest.raises(AIProviderConfigurationError, match="GEMINI_API_KEY"):
        ArtifactGeneratorFactory.create(ARTIFACT_TYPE_QUIZ)
