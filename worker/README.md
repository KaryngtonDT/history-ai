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

## Gemini smoke test (local, manual)

Validates a real Gemini API key without changing the processing pipeline.
Never commit your API key.

### Required environment variables

```env
GEMINI_API_KEY=your_key_here
SUMMARY_GENERATOR=ai
AI_PROVIDER=gemini
AI_EXECUTION_MODE=balanced
```

### Run locally

```bash
cd worker
export GEMINI_API_KEY=your_key_here
export SUMMARY_GENERATOR=ai
export AI_PROVIDER=gemini
export AI_EXECUTION_MODE=balanced
uv run python scripts/smoke_test_gemini.py
```

### Run in Docker

```bash
docker compose exec \
  -e GEMINI_API_KEY=your_key_here \
  -e SUMMARY_GENERATOR=ai \
  -e AI_PROVIDER=gemini \
  -e AI_EXECUTION_MODE=balanced \
  worker uv run python scripts/smoke_test_gemini.py
```

PowerShell example:

```powershell
docker compose exec `
  -e GEMINI_API_KEY=$env:GEMINI_API_KEY `
  -e SUMMARY_GENERATOR=ai `
  -e AI_PROVIDER=gemini `
  -e AI_EXECUTION_MODE=balanced `
  worker uv run python scripts/smoke_test_gemini.py
```

### Expected output

```text
provider: gemini
model: gemini-flash
text: <short generated summary>
```

The command prints only provider, model, and generated text. It never prints
`GEMINI_API_KEY`.
