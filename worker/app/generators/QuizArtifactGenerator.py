from app.ai.AIProviderInterface import AIProviderInterface
from app.generators.ArtifactGenerationRequest import ArtifactGenerationRequest
from app.generators.ArtifactGenerationResult import ArtifactGenerationResult
from app.generators.ArtifactGeneratorConfigurationError import (
    ArtifactGeneratorConfigurationError,
)
from app.generators.ArtifactGeneratorInterface import ArtifactGeneratorInterface
from app.generators.ArtifactType import ARTIFACT_TYPE_QUIZ
from app.generators.QuizGenerationError import QuizGenerationError

DEFAULT_QUESTION_COUNT = 5


class QuizArtifactGenerator(ArtifactGeneratorInterface):
    QUIZ_PROMPT_TEMPLATE = (
        "Generate a quiz with exactly {question_count} multiple-choice questions "
        "based on the following transcript.\n"
        "Use Markdown with this structure:\n\n"
        "# Quiz\n\n"
        "## Question 1\n"
        "<question text>\n"
        "- A) <option>\n"
        "- B) <option>\n"
        "- C) <option>\n"
        "- D) <option>\n\n"
        "Answer: <letter>\n\n"
        "Repeat for each question.\n\n"
        "Transcript:\n\n{transcript}"
    )

    def __init__(
        self,
        ai_provider: AIProviderInterface,
        question_count: int = DEFAULT_QUESTION_COUNT,
    ) -> None:
        self._ai_provider = ai_provider
        self._question_count = question_count

    def generate(
        self,
        request: ArtifactGenerationRequest,
    ) -> ArtifactGenerationResult:
        if request.artifact_type != ARTIFACT_TYPE_QUIZ:
            msg = (
                f"Expected artifact type {ARTIFACT_TYPE_QUIZ!r}, "
                f"got {request.artifact_type!r}."
            )
            raise ArtifactGeneratorConfigurationError(msg)

        normalized = request.transcript.strip()
        if not normalized:
            msg = "Transcript is empty; cannot generate quiz."
            raise QuizGenerationError(msg)

        prompt = self.QUIZ_PROMPT_TEMPLATE.format(
            question_count=self._question_count,
            transcript=normalized,
        )
        response = self._ai_provider.generate_text(prompt)
        return ArtifactGenerationResult(
            artifact_type=ARTIFACT_TYPE_QUIZ,
            content=response.text,
        )
