from app.models.ProcessingJob import ProcessingJob
from app.services.ProcessingService import ProcessingService


class ProcessingWorker:
    def __init__(self, service: ProcessingService) -> None:
        self._service = service

    async def execute(self, job: ProcessingJob) -> None:
        await self._service.execute(job.id)
