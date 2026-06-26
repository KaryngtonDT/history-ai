import httpx
from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, Field

from app.models.ProcessingJob import ProcessingJob
from app.repositories.SymfonyApiRepository import SymfonyApiRepository
from app.services.ProcessingService import ProcessingService
from app.workers.ProcessingWorker import ProcessingWorker

router = APIRouter(prefix="/jobs", tags=["jobs"])

_repository = SymfonyApiRepository.from_env()
_service = ProcessingService(_repository)
_worker = ProcessingWorker(_service)


class ExecuteJobRequest(BaseModel):
    processing_job_id: str = Field(alias="processingJobId")
    content_id: str = Field(alias="contentId")
    type: str

    model_config = {"populate_by_name": True}


class ExecuteJobResponse(BaseModel):
    status: str


@router.post("/execute", response_model=ExecuteJobResponse)
async def execute_job(request: ExecuteJobRequest) -> ExecuteJobResponse:
    job = ProcessingJob(
        id=request.processing_job_id,
        content_id=request.content_id,
        type=request.type,
    )

    try:
        await _worker.execute(job)
    except httpx.HTTPError as exc:
        raise HTTPException(status_code=502, detail=str(exc)) from exc

    return ExecuteJobResponse(status="completed")
