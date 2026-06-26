# History AI Worker

Python worker foundation for History AI.

## Stack

- Python 3.13
- FastAPI
- uv
- Pydantic
- Ruff
- Pytest

## Commands

```bash
uv sync
uv run uvicorn app.main:app --reload --host 0.0.0.0 --port 8001
uv run pytest
uv run ruff check .
```

## Health

```bash
curl http://localhost:8001/health
```
