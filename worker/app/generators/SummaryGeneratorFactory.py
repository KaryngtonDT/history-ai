from app.generators.DeterministicSummaryGenerator import DeterministicSummaryGenerator
from app.generators.SummaryGeneratorInterface import SummaryGeneratorInterface


class SummaryGeneratorFactory:
    @staticmethod
    def create() -> SummaryGeneratorInterface:
        return DeterministicSummaryGenerator()
