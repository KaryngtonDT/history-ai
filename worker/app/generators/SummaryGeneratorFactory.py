import os

from app.ai.AIProviderFactory import AIProviderFactory
from app.generators.AISummaryGenerator import AISummaryGenerator
from app.generators.DeterministicSummaryGenerator import DeterministicSummaryGenerator
from app.generators.SummaryGeneratorConfigurationError import (
    SummaryGeneratorConfigurationError,
)
from app.generators.SummaryGeneratorInterface import SummaryGeneratorInterface

SUMMARY_GENERATOR_DETERMINISTIC = "deterministic"
SUMMARY_GENERATOR_AI = "ai"
DEFAULT_SUMMARY_GENERATOR = SUMMARY_GENERATOR_DETERMINISTIC


class SummaryGeneratorFactory:
    @staticmethod
    def create() -> SummaryGeneratorInterface:
        generator_type = os.environ.get(
            "SUMMARY_GENERATOR",
            DEFAULT_SUMMARY_GENERATOR,
        ).strip().lower()

        if generator_type == SUMMARY_GENERATOR_DETERMINISTIC:
            return DeterministicSummaryGenerator()

        if generator_type == SUMMARY_GENERATOR_AI:
            ai_provider = AIProviderFactory.create_from_env()
            return AISummaryGenerator(ai_provider=ai_provider)

        msg = (
            f"Invalid SUMMARY_GENERATOR {generator_type!r}; "
            f"expected {SUMMARY_GENERATOR_DETERMINISTIC!r} or "
            f"{SUMMARY_GENERATOR_AI!r}."
        )
        raise SummaryGeneratorConfigurationError(msg)
