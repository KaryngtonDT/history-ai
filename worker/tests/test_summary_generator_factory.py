from app.generators.DeterministicSummaryGenerator import DeterministicSummaryGenerator
from app.generators.SummaryGeneratorFactory import SummaryGeneratorFactory
from app.generators.SummaryGeneratorInterface import SummaryGeneratorInterface


def test_factory_returns_deterministic_implementation() -> None:
    generator = SummaryGeneratorFactory.create()

    assert isinstance(generator, DeterministicSummaryGenerator)
    assert isinstance(generator, SummaryGeneratorInterface)
