from dataclasses import dataclass


@dataclass(frozen=True)
class AIProviderResponse:
    text: str
    model: str
    provider: str
