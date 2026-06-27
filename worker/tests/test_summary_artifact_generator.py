from unittest.mock import MagicMock

import pytest

from app.generators.ArtifactGenerationRequest import ArtifactGenerationRequest
from app.generators.ArtifactGeneratorConfigurationError import (
    ArtifactGeneratorConfigurationError,
)
from app.generators.ArtifactType import ARTIFACT_TYPE_SUMMARY
from app.generators.SummaryArtifactGenerator import SummaryArtifactGenerator
from app.generators.SummaryGeneratorInterface import SummaryGeneratorInterface


def test_delegates_to_summary_generator() -> None:
    summary_generator = MagicMock(spec=SummaryGeneratorInterface)
    summary_generator.generate.return_value = "Delegated summary."
    artifact_generator = SummaryArtifactGenerator(summary_generator=summary_generator)
    transcript = "The Roman Empire was vast."

    result = artifact_generator.generate(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_SUMMARY,
            transcript=transcript,
        ),
    )

    summary_generator.generate.assert_called_once_with(transcript)
    assert result.artifact_type == ARTIFACT_TYPE_SUMMARY
    assert result.content == "Delegated summary."


def test_rejects_mismatched_artifact_type() -> None:
    summary_generator = MagicMock(spec=SummaryGeneratorInterface)
    artifact_generator = SummaryArtifactGenerator(summary_generator=summary_generator)

    with pytest.raises(
        ArtifactGeneratorConfigurationError,
        match="Expected artifact type 'summary'",
    ):
        artifact_generator.generate(
            ArtifactGenerationRequest(
                artifact_type="quiz",
                transcript="Some transcript.",
            ),
        )

    summary_generator.generate.assert_not_called()


def test_passes_language_and_options_without_using_them_yet() -> None:
    summary_generator = MagicMock(spec=SummaryGeneratorInterface)
    summary_generator.generate.return_value = "Summary text."
    artifact_generator = SummaryArtifactGenerator(summary_generator=summary_generator)

    result = artifact_generator.generate(
        ArtifactGenerationRequest(
            artifact_type=ARTIFACT_TYPE_SUMMARY,
            transcript="Transcript text.",
            language="fr",
            options={"style": "concise"},
        ),
    )

    summary_generator.generate.assert_called_once_with("Transcript text.")
    assert result.content == "Summary text."
