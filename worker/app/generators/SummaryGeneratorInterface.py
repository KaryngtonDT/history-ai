from abc import ABC, abstractmethod


class SummaryGeneratorInterface(ABC):
    @abstractmethod
    def generate(self, transcript: str) -> str:
        """Generate a summary from the given transcript text."""
