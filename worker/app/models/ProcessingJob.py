from dataclasses import dataclass


@dataclass(frozen=True)
class ProcessingJob:
    id: str
    content_id: str = ""
    type: str = ""
