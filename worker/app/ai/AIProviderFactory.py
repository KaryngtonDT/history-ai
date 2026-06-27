from app.ai.AIProviderInterface import AIProviderInterface
from app.ai.MockAIProvider import MockAIProvider


class AIProviderFactory:
    @staticmethod
    def create() -> AIProviderInterface:
        return MockAIProvider()
