import os

import httpx

from app.ai.AIExecutionMode import AIExecutionMode
from app.ai.AIModel import AIModel
from app.ai.AIProviderConfigurationError import AIProviderConfigurationError
from app.ai.AIProviderHttpError import AIProviderHttpError
from app.ai.AIProviderInterface import AIProviderInterface
from app.ai.AIProviderResponse import AIProviderResponse
from app.ai.AIProviderType import AIProviderType

GEMINI_FLASH_MODEL = AIModel(
    provider=AIProviderType.GEMINI,
    model_name="gemini-flash",
    supports_streaming=True,
    supports_json=True,
    max_context=1_000_000,
)

GEMINI_PRO_MODEL = AIModel(
    provider=AIProviderType.GEMINI,
    model_name="gemini-pro",
    supports_streaming=True,
    supports_json=True,
    max_context=2_000_000,
)

GEMINI_MODELS: dict[str, AIModel] = {
    GEMINI_FLASH_MODEL.model_name: GEMINI_FLASH_MODEL,
    GEMINI_PRO_MODEL.model_name: GEMINI_PRO_MODEL,
}

MODE_TO_GEMINI_MODEL: dict[AIExecutionMode, AIModel] = {
    AIExecutionMode.FAST: GEMINI_FLASH_MODEL,
    AIExecutionMode.BALANCED: GEMINI_FLASH_MODEL,
    AIExecutionMode.QUALITY: GEMINI_PRO_MODEL,
    AIExecutionMode.CHEAP: GEMINI_FLASH_MODEL,
    AIExecutionMode.LOCAL: GEMINI_FLASH_MODEL,
}

MODEL_NAME_TO_API_ID: dict[str, str] = {
    "gemini-flash": "gemini-2.5-flash",
    "gemini-pro": "gemini-2.5-pro",
}


class GeminiProvider(AIProviderInterface):
    API_BASE_URL = "https://generativelanguage.googleapis.com/v1beta"
    PROVIDER_NAME = "gemini"

    def __init__(
        self,
        model: AIModel,
        *,
        api_key: str | None = None,
        http_client: httpx.Client | None = None,
    ) -> None:
        if model.provider is not AIProviderType.GEMINI:
            msg = "GeminiProvider requires a Gemini AIModel."
            raise AIProviderConfigurationError(msg)
        self._model = model
        self._api_key = (
            api_key if api_key is not None else os.environ.get("GEMINI_API_KEY", "")
        ).strip()
        self._http_client = http_client

    @property
    def model(self) -> AIModel:
        return self._model

    def generate_text(self, prompt: str) -> AIProviderResponse:
        normalized = prompt.strip()

        if not normalized:
            msg = "Prompt is empty."
            raise AIProviderConfigurationError(msg)

        if not self._api_key:
            msg = "GEMINI_API_KEY is not configured."
            raise AIProviderConfigurationError(msg)

        api_model_id = MODEL_NAME_TO_API_ID.get(self._model.model_name)
        if api_model_id is None:
            msg = f"Unknown Gemini model {self._model.model_name!r}."
            raise AIProviderConfigurationError(msg)

        url = f"{self.API_BASE_URL}/models/{api_model_id}:generateContent"
        payload = {"contents": [{"parts": [{"text": normalized}]}]}
        headers = {
            "Content-Type": "application/json",
            "x-goog-api-key": self._api_key,
        }

        own_client = self._http_client is None
        client = self._http_client or httpx.Client(timeout=60.0)

        try:
            response = client.post(url, headers=headers, json=payload)
            response.raise_for_status()
        except httpx.HTTPError as exc:
            msg = f"Gemini API request failed: {exc}"
            raise AIProviderHttpError(msg) from exc
        finally:
            if own_client:
                client.close()

        try:
            text = self._extract_text_from_response(response.json())
        except (IndexError, KeyError, TypeError, ValueError) as exc:
            msg = "Gemini API returned an unexpected response payload."
            raise AIProviderHttpError(msg) from exc

        return AIProviderResponse(
            text=text,
            model=self._model.model_name,
            provider=self.PROVIDER_NAME,
        )

    @staticmethod
    def _extract_text_from_response(payload: dict) -> str:
        candidates = payload["candidates"]
        parts = candidates[0]["content"]["parts"]
        text = parts[0]["text"]
        if not isinstance(text, str) or not text.strip():
            msg = "Gemini API returned empty text."
            raise ValueError(msg)
        return text.strip()
