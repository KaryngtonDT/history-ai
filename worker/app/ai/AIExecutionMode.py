from enum import StrEnum


class AIExecutionMode(StrEnum):
    FAST = "fast"
    BALANCED = "balanced"
    QUALITY = "quality"
    CHEAP = "cheap"
    LOCAL = "local"
