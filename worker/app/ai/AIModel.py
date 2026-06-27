from dataclasses import dataclass

from app.ai.AIProviderType import AIProviderType


@dataclass(frozen=True)
class AIModel:
    provider: AIProviderType
    model_name: str
    supports_streaming: bool
    supports_json: bool
    max_context: int
