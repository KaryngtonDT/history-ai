from dataclasses import dataclass


@dataclass(frozen=True)
class ArtifactGenerationResult:
    artifact_type: str
    content: str
