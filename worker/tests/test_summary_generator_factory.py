import pytest

from app.ai.AIProviderConfigurationError import AIProviderConfigurationError
from app.generators.AISummaryGenerator import AISummaryGenerator
from app.generators.DeterministicSummaryGenerator import DeterministicSummaryGenerator
from app.generators.SummaryGeneratorConfigurationError import (
    SummaryGeneratorConfigurationError,
)
from app.generators.SummaryGeneratorFactory import SummaryGeneratorFactory
from app.generators.SummaryGeneratorInterface import SummaryGeneratorInterface


def test_factory_returns_deterministic_implementation_by_default(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.delenv("SUMMARY_GENERATOR", raising=False)

    generator = SummaryGeneratorFactory.create()

    assert isinstance(generator, DeterministicSummaryGenerator)
    assert isinstance(generator, SummaryGeneratorInterface)


def test_factory_returns_deterministic_when_configured(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("SUMMARY_GENERATOR", "deterministic")

    generator = SummaryGeneratorFactory.create()

    assert isinstance(generator, DeterministicSummaryGenerator)


def test_factory_returns_ai_summary_generator_with_mock_provider(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("SUMMARY_GENERATOR", "ai")
    monkeypatch.setenv("AI_PROVIDER", "mock")
    monkeypatch.setenv("AI_EXECUTION_MODE", "balanced")

    generator = SummaryGeneratorFactory.create()

    assert isinstance(generator, AISummaryGenerator)
    assert generator.generate("Roman Empire.").startswith("Mock response:")


def test_ai_mock_mode_generates_summary_without_network(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("SUMMARY_GENERATOR", "ai")
    monkeypatch.setenv("AI_PROVIDER", "mock")
    monkeypatch.setenv("AI_EXECUTION_MODE", "balanced")

    generator = SummaryGeneratorFactory.create()
    transcript = "The Roman Empire was vast."

    summary = generator.generate(transcript)

    assert summary.startswith("Mock response:")
    assert transcript in summary


def test_gemini_selected_without_api_key_fails_clearly(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("SUMMARY_GENERATOR", "ai")
    monkeypatch.setenv("AI_PROVIDER", "gemini")
    monkeypatch.setenv("AI_EXECUTION_MODE", "balanced")
    monkeypatch.setenv("GEMINI_API_KEY", "")

    with pytest.raises(AIProviderConfigurationError, match="GEMINI_API_KEY"):
        SummaryGeneratorFactory.create()


def test_invalid_summary_generator_raises_configuration_error(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("SUMMARY_GENERATOR", "unknown")

    with pytest.raises(
        SummaryGeneratorConfigurationError,
        match="Invalid SUMMARY_GENERATOR",
    ):
        SummaryGeneratorFactory.create()


def test_invalid_ai_provider_raises_configuration_error(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("SUMMARY_GENERATOR", "ai")
    monkeypatch.setenv("AI_PROVIDER", "not-a-provider")

    with pytest.raises(AIProviderConfigurationError, match="Invalid AI_PROVIDER"):
        SummaryGeneratorFactory.create()


def test_invalid_ai_execution_mode_raises_configuration_error(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("SUMMARY_GENERATOR", "ai")
    monkeypatch.setenv("AI_PROVIDER", "mock")
    monkeypatch.setenv("AI_EXECUTION_MODE", "turbo")

    with pytest.raises(AIProviderConfigurationError, match="Invalid AI_EXECUTION_MODE"):
        SummaryGeneratorFactory.create()
