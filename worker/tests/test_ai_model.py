from app.ai.AIProviderType import AIProviderType
from app.ai.MockAIProvider import (
    MOCK_BALANCED_MODEL,
    MOCK_FAST_MODEL,
    MOCK_QUALITY_MODEL,
)


def test_mock_fast_model_metadata() -> None:
    assert MOCK_FAST_MODEL.provider is AIProviderType.MOCK
    assert MOCK_FAST_MODEL.model_name == "mock-fast"
    assert MOCK_FAST_MODEL.supports_streaming is True
    assert MOCK_FAST_MODEL.supports_json is True
    assert MOCK_FAST_MODEL.max_context == 128_000


def test_mock_balanced_model_metadata() -> None:
    assert MOCK_BALANCED_MODEL.provider is AIProviderType.MOCK
    assert MOCK_BALANCED_MODEL.model_name == "mock-balanced"
    assert MOCK_BALANCED_MODEL.supports_streaming is True
    assert MOCK_BALANCED_MODEL.supports_json is True
    assert MOCK_BALANCED_MODEL.max_context == 256_000


def test_mock_quality_model_metadata() -> None:
    assert MOCK_QUALITY_MODEL.provider is AIProviderType.MOCK
    assert MOCK_QUALITY_MODEL.model_name == "mock-quality"
    assert MOCK_QUALITY_MODEL.supports_streaming is True
    assert MOCK_QUALITY_MODEL.supports_json is False
    assert MOCK_QUALITY_MODEL.max_context == 512_000
