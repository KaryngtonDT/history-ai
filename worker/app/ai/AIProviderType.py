from enum import StrEnum


class AIProviderType(StrEnum):
    AUTO = "auto"
    MOCK = "mock"
    GEMINI = "gemini"
    OPENAI = "openai"
    ANTHROPIC = "anthropic"
    OLLAMA = "ollama"
    LMSTUDIO = "lmstudio"
    OPENROUTER = "openrouter"
