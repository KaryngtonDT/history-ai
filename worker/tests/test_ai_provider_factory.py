import pytest

from app.ai.AIExecutionMode import AIExecutionMode
from app.ai.AIProviderFactory import AIProviderFactory, UnsupportedAIProviderError
from app.ai.AIProviderInterface import AIProviderInterface
from app.ai.AIProviderType import AIProviderType
from app.ai.MockAIProvider import MOCK_BALANCED_MODEL, MockAIProvider


def test_factory_returns_mock_provider() -> None:
    provider = AIProviderFactory.create(
        provider=AIProviderType.MOCK,
        mode=AIExecutionMode.BALANCED,
    )

    assert isinstance(provider, MockAIProvider)
    assert isinstance(provider, AIProviderInterface)
    assert provider.model == MOCK_BALANCED_MODEL


@pytest.mark.parametrize("provider_type", list(AIProviderType))
def test_factory_supports_all_provider_enums(
    provider_type: AIProviderType,
) -> None:
    if provider_type in {AIProviderType.AUTO, AIProviderType.MOCK}:
        provider = AIProviderFactory.create(
            provider=provider_type,
            mode=AIExecutionMode.BALANCED,
        )
        assert isinstance(provider, MockAIProvider)
        assert provider.model.model_name == "mock-balanced"
        return

    with pytest.raises(UnsupportedAIProviderError, match="not implemented yet"):
        AIProviderFactory.create(
            provider=provider_type,
            mode=AIExecutionMode.BALANCED,
        )


@pytest.mark.parametrize(
    ("mode", "expected_model_name"),
    [
        (AIExecutionMode.FAST, "mock-fast"),
        (AIExecutionMode.BALANCED, "mock-balanced"),
        (AIExecutionMode.QUALITY, "mock-quality"),
        (AIExecutionMode.CHEAP, "mock-fast"),
        (AIExecutionMode.LOCAL, "mock-balanced"),
    ],
)
def test_factory_supports_all_execution_modes(
    mode: AIExecutionMode,
    expected_model_name: str,
) -> None:
    provider = AIProviderFactory.create(
        provider=AIProviderType.MOCK,
        mode=mode,
    )

    assert provider.model.model_name == expected_model_name


@pytest.mark.parametrize("mode", list(AIExecutionMode))
def test_auto_resolves_to_mock_provider_with_mode_model(
    mode: AIExecutionMode,
) -> None:
    provider = AIProviderFactory.create(
        provider=AIProviderType.AUTO,
        mode=mode,
    )
    expected_model = AIProviderFactory.resolve_model(AIProviderType.MOCK, mode)

    assert isinstance(provider, MockAIProvider)
    assert provider.model == expected_model
    assert provider.generate_text("Test.").provider == "mock"


def test_unsupported_provider_raises_explicit_exception() -> None:
    with pytest.raises(UnsupportedAIProviderError, match="gemini"):
        AIProviderFactory.create(
            provider=AIProviderType.GEMINI,
            mode=AIExecutionMode.FAST,
        )
