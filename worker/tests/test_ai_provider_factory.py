from app.ai.AIProviderFactory import AIProviderFactory
from app.ai.AIProviderInterface import AIProviderInterface
from app.ai.MockAIProvider import MockAIProvider


def test_factory_returns_mock_provider() -> None:
    provider = AIProviderFactory.create()

    assert isinstance(provider, MockAIProvider)
    assert isinstance(provider, AIProviderInterface)
