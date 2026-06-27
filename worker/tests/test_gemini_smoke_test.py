from unittest.mock import MagicMock

import pytest

from app.cli.gemini_smoke_test import (
    DEFAULT_SMOKE_TRANSCRIPT,
    GeminiSmokeTestConfigurationError,
    GeminiSmokeTestResult,
    format_smoke_test_output,
    run_smoke_test,
    truncate_text,
    validate_environment,
)
from app.generators.SummaryGeneratorInterface import SummaryGeneratorInterface


def test_validate_environment_requires_gemini_api_key(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("SUMMARY_GENERATOR", "ai")
    monkeypatch.setenv("AI_PROVIDER", "gemini")
    monkeypatch.delenv("GEMINI_API_KEY", raising=False)

    with pytest.raises(GeminiSmokeTestConfigurationError, match="GEMINI_API_KEY"):
        validate_environment()


def test_validate_environment_requires_ai_summary_mode(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("GEMINI_API_KEY", "test-key")
    monkeypatch.setenv("SUMMARY_GENERATOR", "deterministic")
    monkeypatch.setenv("AI_PROVIDER", "gemini")

    with pytest.raises(GeminiSmokeTestConfigurationError, match="SUMMARY_GENERATOR"):
        validate_environment()


def test_validate_environment_requires_gemini_provider(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("GEMINI_API_KEY", "test-key")
    monkeypatch.setenv("SUMMARY_GENERATOR", "ai")
    monkeypatch.setenv("AI_PROVIDER", "mock")

    with pytest.raises(GeminiSmokeTestConfigurationError, match="AI_PROVIDER"):
        validate_environment()


def test_run_smoke_test_uses_summary_generator_factory(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setenv("GEMINI_API_KEY", "super-secret-key")
    monkeypatch.setenv("SUMMARY_GENERATOR", "ai")
    monkeypatch.setenv("AI_PROVIDER", "gemini")
    monkeypatch.setenv("AI_EXECUTION_MODE", "balanced")

    generator = MagicMock(spec=SummaryGeneratorInterface)
    generator.generate.return_value = "Gemini generated summary text."

    def generator_factory() -> SummaryGeneratorInterface:
        return generator

    result = run_smoke_test(generator_factory=generator_factory)

    generator.generate.assert_called_once_with(DEFAULT_SMOKE_TRANSCRIPT)
    assert result == GeminiSmokeTestResult(
        provider="gemini",
        model="gemini-flash",
        text="Gemini generated summary text.",
    )


def test_format_output_does_not_expose_api_key(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    api_key = "super-secret-key-not-in-output"
    monkeypatch.setenv("GEMINI_API_KEY", api_key)

    output = format_smoke_test_output(
        GeminiSmokeTestResult(
            provider="gemini",
            model="gemini-flash",
            text="Short summary.",
        ),
    )

    assert api_key not in output
    assert "provider: gemini" in output
    assert "model: gemini-flash" in output
    assert "text: Short summary." in output


def test_truncate_text_limits_long_output() -> None:
    long_text = "a" * 600

    truncated = truncate_text(long_text, max_length=100)

    assert len(truncated) == 100
    assert truncated.endswith("...")
