from app.ai.AIProviderInterface import AIProviderInterface
from app.generators.SummaryGenerationError import SummaryGenerationError
from app.generators.SummaryGeneratorInterface import SummaryGeneratorInterface


class AISummaryGenerator(SummaryGeneratorInterface):
    SUMMARY_PROMPT_TEMPLATE = (
        "Summarize the following transcript in a concise paragraph:\n\n{transcript}"
    )

    def __init__(self, ai_provider: AIProviderInterface) -> None:
        self._ai_provider = ai_provider

    def generate(self, transcript: str) -> str:
        normalized = transcript.strip()

        if not normalized:
            msg = "Transcript is empty; cannot generate summary."
            raise SummaryGenerationError(msg)

        prompt = self.SUMMARY_PROMPT_TEMPLATE.format(transcript=normalized)
        response = self._ai_provider.generate_text(prompt)
        return response.text
