import pytest

from app.generators.ArtifactGenerationRequest import ArtifactGenerationRequest
from app.generators.ArtifactGenerationRequestError import ArtifactGenerationRequestError
from app.generators.ArtifactType import ARTIFACT_TYPE_SUMMARY


def test_request_exposes_required_fields() -> None:
    request = ArtifactGenerationRequest(
        artifact_type=ARTIFACT_TYPE_SUMMARY,
        transcript="The Roman Empire was vast.",
        language="en",
        options={"style": "concise"},
    )

    assert request.artifact_type == ARTIFACT_TYPE_SUMMARY
    assert request.transcript == "The Roman Empire was vast."
    assert request.language == "en"
    assert request.options == {"style": "concise"}


def test_request_defaults_language_and_options() -> None:
    request = ArtifactGenerationRequest(
        artifact_type=ARTIFACT_TYPE_SUMMARY,
        transcript="Transcript text.",
    )

    assert request.language is None
    assert request.options == {}


def test_request_is_immutable() -> None:
    request = ArtifactGenerationRequest(
        artifact_type=ARTIFACT_TYPE_SUMMARY,
        transcript="Transcript text.",
    )

    with pytest.raises(AttributeError):
        request.transcript = "Changed."


def test_rejects_empty_artifact_type() -> None:
    with pytest.raises(ArtifactGenerationRequestError, match="artifact_type"):
        ArtifactGenerationRequest(
            artifact_type="   ",
            transcript="Transcript text.",
        )


def test_rejects_empty_transcript() -> None:
    with pytest.raises(ArtifactGenerationRequestError, match="transcript"):
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_SUMMARY,
            transcript="",
        )


def test_rejects_blank_language_when_provided() -> None:
    with pytest.raises(ArtifactGenerationRequestError, match="language"):
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_SUMMARY,
            transcript="Transcript text.",
            language="   ",
        )
