import httpx
import pytest

from app.ai.AIExecutionMode import AIExecutionMode
from app.ai.AIProviderConfigurationError import AIProviderConfigurationError
from app.ai.AIProviderFactory import AIProviderFactory
from app.ai.AIProviderHttpError import AIProviderHttpError
from app.ai.AIProviderType import AIProviderType
from app.ai.GeminiProvider import (
    GEMINI_FLASH_MODEL,
    GEMINI_MODELS,
    GEMINI_PRO_MODEL,
    GeminiProvider,
)


def test_factory_returns_gemini_provider() -> None:
    provider = AIProviderFactory.create(
        provider=AIProviderType.GEMINI,
        mode=AIExecutionMode.BALANCED,
    )

    assert isinstance(provider, GeminiProvider)
    assert provider.model == GEMINI_FLASH_MODEL


def test_exposes_gemini_model_metadata() -> None:
    assert set(GEMINI_MODELS) == {"gemini-flash", "gemini-pro"}
    assert GEMINI_FLASH_MODEL.provider is AIProviderType.GEMINI
    assert GEMINI_PRO_MODEL.provider is AIProviderType.GEMINI
    assert GEMINI_FLASH_MODEL.supports_streaming is True
    assert GEMINI_PRO_MODEL.max_context == 2_000_000


@pytest.mark.parametrize(
    ("mode", "expected_model_name"),
    [
        (AIExecutionMode.FAST, "gemini-flash"),
        (AIExecutionMode.BALANCED, "gemini-flash"),
        (AIExecutionMode.QUALITY, "gemini-pro"),
        (AIExecutionMode.CHEAP, "gemini-flash"),
        (AIExecutionMode.LOCAL, "gemini-flash"),
    ],
)
def test_factory_selects_gemini_model_by_mode(
    mode: AIExecutionMode,
    expected_model_name: str,
) -> None:
    provider = AIProviderFactory.create(
        provider=AIProviderType.GEMINI,
        mode=mode,
    )

    assert provider.model.model_name == expected_model_name


def test_generate_text_calls_gemini_api_with_mocked_http() -> None:
    captured: dict[str, object] = {}

    def handler(request: httpx.Request) -> httpx.Response:
        captured["url"] = str(request.url)
        captured["headers"] = dict(request.headers)
        captured["body"] = request.content.decode()
        return httpx.Response(
            200,
            json={
                "candidates": [
                    {
                        "content": {
                            "parts": [{"text": "Generated summary text."}],
                        },
                    },
                ],
            },
        )

    client = httpx.Client(transport=httpx.MockTransport(handler))
    provider = GeminiProvider(
        GEMINI_FLASH_MODEL,
        api_key="test-gemini-key",
        http_client=client,
    )

    response = provider.generate_text("Summarize the Roman Empire.")

    assert response.text == "Generated summary text."
    assert response.model == "gemini-flash"
    assert response.provider == "gemini"
    assert "models/gemini-2.5-flash:generateContent" in str(captured["url"])
    assert captured["headers"]["x-goog-api-key"] == "test-gemini-key"
    assert "Summarize the Roman Empire." in str(captured["body"])


def test_generate_text_uses_pro_model_mapping() -> None:
    captured: dict[str, str] = {}

    def handler(request: httpx.Request) -> httpx.Response:
        captured["url"] = str(request.url)
        return httpx.Response(
            200,
            json={
                "candidates": [
                    {"content": {"parts": [{"text": "High quality summary."}]}},
                ],
            },
        )

    client = httpx.Client(transport=httpx.MockTransport(handler))
    provider = GeminiProvider(
        GEMINI_PRO_MODEL,
        api_key="test-gemini-key",
        http_client=client,
    )

    response = provider.generate_text("Summarize the Roman Empire.")

    assert response.text == "High quality summary."
    assert "models/gemini-2.5-pro:generateContent" in captured["url"]


def test_missing_api_key_raises_configuration_error() -> None:
    provider = GeminiProvider(GEMINI_FLASH_MODEL, api_key="")

    with pytest.raises(AIProviderConfigurationError, match="GEMINI_API_KEY"):
        provider.generate_text("Summarize the Roman Empire.")


def test_empty_prompt_raises_configuration_error() -> None:
    provider = GeminiProvider(GEMINI_FLASH_MODEL, api_key="test-gemini-key")

    with pytest.raises(AIProviderConfigurationError, match="Prompt is empty"):
        provider.generate_text("   ")


def test_http_error_raises_ai_provider_http_error() -> None:
    def handler(_request: httpx.Request) -> httpx.Response:
        return httpx.Response(503, json={"error": {"message": "Unavailable"}})

    client = httpx.Client(transport=httpx.MockTransport(handler))
    provider = GeminiProvider(
        GEMINI_FLASH_MODEL,
        api_key="test-gemini-key",
        http_client=client,
    )

    with pytest.raises(AIProviderHttpError, match="Gemini API request failed"):
        provider.generate_text("Summarize the Roman Empire.")


def test_invalid_response_payload_raises_http_error() -> None:
    def handler(_request: httpx.Request) -> httpx.Response:
        return httpx.Response(200, json={"candidates": []})

    client = httpx.Client(transport=httpx.MockTransport(handler))
    provider = GeminiProvider(
        GEMINI_FLASH_MODEL,
        api_key="test-gemini-key",
        http_client=client,
    )

    with pytest.raises(AIProviderHttpError, match="unexpected response payload"):
        provider.generate_text("Summarize the Roman Empire.")
