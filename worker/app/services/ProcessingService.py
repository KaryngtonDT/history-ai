import asyncio
from collections.abc import Awaitable, Callable

from app.generators.ArtifactGenerationRequest import ArtifactGenerationRequest
from app.generators.ArtifactGeneratorFactory import ArtifactGeneratorFactory
from app.generators.ArtifactGeneratorInterface import ArtifactGeneratorInterface
from app.generators.ArtifactType import ARTIFACT_TYPE_QUIZ
from app.generators.SummaryGeneratorFactory import SummaryGeneratorFactory
from app.generators.SummaryGeneratorInterface import SummaryGeneratorInterface
from app.models.ProcessingJob import ProcessingJob
from app.repositories.SymfonyApiRepository import SymfonyApiRepository
from app.services.DocumentExtractionService import DocumentExtractionService

SleepFn = Callable[[float], Awaitable[None]]
CreateArtifactGeneratorFn = Callable[[str], ArtifactGeneratorInterface]


async def _default_sleep(seconds: float) -> None:
    await asyncio.sleep(seconds)


class ProcessingService:
    PROGRESS_STEPS = (20, 45, 80)
    SLEEP_SECONDS = 2.0
    SUMMARY_ARTIFACT_TYPE = "summary"
    TRANSCRIPT_ARTIFACT_TYPE = "transcript"
    QUIZ_ARTIFACT_TYPE = ARTIFACT_TYPE_QUIZ

    def __init__(
        self,
        repository: SymfonyApiRepository,
        document_extraction: DocumentExtractionService | None = None,
        summary_generator: SummaryGeneratorInterface | None = None,
        create_artifact_generator: CreateArtifactGeneratorFn | None = None,
        sleep_fn: SleepFn = _default_sleep,
    ) -> None:
        self._repository = repository
        self._document_extraction = (
            document_extraction or DocumentExtractionService()
        )
        self._summary_generator = (
            summary_generator or SummaryGeneratorFactory.create()
        )
        self._create_artifact_generator = (
            create_artifact_generator or ArtifactGeneratorFactory.create
        )
        self._sleep = sleep_fn

    async def execute(self, job: ProcessingJob) -> None:
        await asyncio.to_thread(self._repository.start, job.id)

        for progress in self.PROGRESS_STEPS:
            await self._sleep(self.SLEEP_SECONDS)
            await asyncio.to_thread(
                self._repository.update_progress,
                job.id,
                progress,
            )

        if job.type == self.SUMMARY_ARTIFACT_TYPE:
            transcript = await asyncio.to_thread(
                self._document_extraction.extract_transcript,
                job.content_id,
            )
            summary = await asyncio.to_thread(
                self._summary_generator.generate,
                transcript,
            )
            quiz = await asyncio.to_thread(
                self._generate_quiz,
                transcript,
            )
            await asyncio.to_thread(
                self._repository.create_artifact,
                job.content_id,
                job.id,
                self.TRANSCRIPT_ARTIFACT_TYPE,
                transcript,
            )
            await asyncio.to_thread(
                self._repository.create_artifact,
                job.content_id,
                job.id,
                self.SUMMARY_ARTIFACT_TYPE,
                summary,
            )
            await asyncio.to_thread(
                self._repository.create_artifact,
                job.content_id,
                job.id,
                self.QUIZ_ARTIFACT_TYPE,
                quiz,
            )

        await self._sleep(self.SLEEP_SECONDS)
        await asyncio.to_thread(self._repository.complete, job.id)

    def _generate_quiz(self, transcript: str) -> str:
        quiz_generator = self._create_artifact_generator(self.QUIZ_ARTIFACT_TYPE)
        result = quiz_generator.generate(
            ArtifactGenerationRequest(
                artifact_type=self.QUIZ_ARTIFACT_TYPE,
                transcript=transcript,
            ),
        )
        return result.content
