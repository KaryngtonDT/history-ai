import pytest

from app.generators.DeterministicSummaryGenerator import (
    DeterministicSummaryGenerator,
    SummaryGenerationError,
)


def test_returns_first_three_meaningful_sentences() -> None:
    generator = DeterministicSummaryGenerator()
    transcript = (
        "The Roman Empire was vast. It lasted many centuries. "
        "Its legacy shaped Europe. Modern law still reflects Roman ideas. "
        "Archaeology continues to reveal new sites."
    )

    summary = generator.generate(transcript)

    assert summary == (
        "The Roman Empire was vast. It lasted many centuries. "
        "Its legacy shaped Europe."
    )


def test_handles_short_transcript_with_fewer_than_three_sentences() -> None:
    generator = DeterministicSummaryGenerator()
    transcript = "The Roman Empire was a vast civilization."

    summary = generator.generate(transcript)

    assert summary == "The Roman Empire was a vast civilization."


def test_rejects_empty_transcript() -> None:
    generator = DeterministicSummaryGenerator()

    with pytest.raises(SummaryGenerationError, match="Transcript is empty"):
        generator.generate("")


def test_rejects_whitespace_only_transcript() -> None:
    generator = DeterministicSummaryGenerator()

    with pytest.raises(SummaryGenerationError, match="Transcript is empty"):
        generator.generate("   \n\t  ")
