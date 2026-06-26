import asyncio
from collections.abc import Awaitable, Callable

from app.repositories.SymfonyApiRepository import SymfonyApiRepository

SleepFn = Callable[[float], Awaitable[None]]


async def _default_sleep(seconds: float) -> None:
    await asyncio.sleep(seconds)


class ProcessingService:
    PROGRESS_STEPS = (20, 45, 80)
    SLEEP_SECONDS = 2.0

    def __init__(
        self,
        repository: SymfonyApiRepository,
        sleep_fn: SleepFn = _default_sleep,
    ) -> None:
        self._repository = repository
        self._sleep = sleep_fn

    async def execute(self, job_id: str) -> None:
        await asyncio.to_thread(self._repository.start, job_id)

        for progress in self.PROGRESS_STEPS:
            await self._sleep(self.SLEEP_SECONDS)
            await asyncio.to_thread(
                self._repository.update_progress,
                job_id,
                progress,
            )

        await self._sleep(self.SLEEP_SECONDS)
        await asyncio.to_thread(self._repository.complete, job_id)
