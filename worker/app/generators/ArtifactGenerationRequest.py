from dataclasses import dataclass, field

from app.generators.ArtifactGenerationRequestError import ArtifactGenerationRequestError


@dataclass(frozen=True)
class ArtifactGenerationRequest:
    artifact_type: str
    transcript: str
    language: str | None = None
    options: dict[str, str] = field(default_factory=dict)

    def __post_init__(self) -> None:
        if not self.artifact_type.strip():
            msg = "artifact_type must not be empty."
            raise ArtifactGenerationRequestError(msg)

        if not self.transcript.strip():
            msg = "transcript must not be empty."
            raise ArtifactGenerationRequestError(msg)

        if self.language is not None and not self.language.strip():
            msg = "language must not be blank when provided."
            raise ArtifactGenerationRequestError(msg)
