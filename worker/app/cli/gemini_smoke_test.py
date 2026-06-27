import os
from collections.abc import Callable
from dataclasses import dataclass

from app.ai.AIProviderFactory import AIProviderFactory
from app.generators.SummaryGeneratorFactory import SummaryGeneratorFactory
from app.generators.SummaryGeneratorInterface import SummaryGeneratorInterface

DEFAULT_SMOKE_TRANSCRIPT = (
    "The Roman Empire was vast. It lasted many centuries. "
    "Its legacy shaped Europe."
)
MAX_OUTPUT_TEXT_LENGTH = 500
REQUIRED_SUMMARY_GENERATOR = "ai"
REQUIRED_AI_PROVIDER = "gemini"


class GeminiSmokeTestConfigurationError(Exception):
    """Raised when Gemini smoke test environment is invalid."""


@dataclass(frozen=True)
class GeminiSmokeTestResult:
    provider: str
    model: str
    text: str


def validate_environment() -> None:
    if not os.environ.get("GEMINI_API_KEY", "").strip():
        msg = "GEMINI_API_KEY is not configured."
        raise GeminiSmokeTestConfigurationError(msg)

    summary_generator = os.environ.get(
        "SUMMARY_GENERATOR",
        "deterministic",
    ).strip().lower()
    if summary_generator != REQUIRED_SUMMARY_GENERATOR:
        msg = "SUMMARY_GENERATOR must be 'ai'."
        raise GeminiSmokeTestConfigurationError(msg)

    ai_provider = os.environ.get("AI_PROVIDER", "mock").strip().lower()
    if ai_provider != REQUIRED_AI_PROVIDER:
        msg = "AI_PROVIDER must be 'gemini'."
        raise GeminiSmokeTestConfigurationError(msg)


def truncate_text(text: str, max_length: int = MAX_OUTPUT_TEXT_LENGTH) -> str:
    normalized = text.strip()

    if len(normalized) <= max_length:
        return normalized

    return f"{normalized[: max_length - 3].rstrip()}..."


def format_smoke_test_output(result: GeminiSmokeTestResult) -> str:
    return (
        f"provider: {result.provider}\n"
        f"model: {result.model}\n"
        f"text: {result.text}"
    )


def run_smoke_test(
    transcript: str = DEFAULT_SMOKE_TRANSCRIPT,
    generator_factory: Callable[[], SummaryGeneratorInterface] | None = None,
) -> GeminiSmokeTestResult:
    validate_environment()

    provider_type = AIProviderFactory.parse_provider_from_env(
        os.environ.get("AI_PROVIDER"),
    )
    mode = AIProviderFactory.parse_execution_mode_from_env(
        os.environ.get("AI_EXECUTION_MODE"),
    )
    model = AIProviderFactory.resolve_model(provider_type, mode)

    create_generator = generator_factory or SummaryGeneratorFactory.create
    generated_text = create_generator().generate(transcript)

    return GeminiSmokeTestResult(
        provider=model.provider.value,
        model=model.model_name,
        text=truncate_text(generated_text),
    )


def main() -> int:
    try:
        result = run_smoke_test()
    except GeminiSmokeTestConfigurationError as exc:
        print(f"error: {exc}")
        return 1
    except Exception as exc:
        print(f"error: Gemini smoke test failed: {exc}")
        return 1

    print(format_smoke_test_output(result))
    return 0
