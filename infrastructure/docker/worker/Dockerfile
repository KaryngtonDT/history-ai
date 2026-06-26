# Python worker — FastAPI on port 8001, reproducible build via uv.
# Build context: repository root.

FROM python:3.13-slim

RUN apt-get update \
    && apt-get install -y --no-install-recommends wget \
    && rm -rf /var/lib/apt/lists/*

COPY --from=ghcr.io/astral-sh/uv:latest /uv /usr/local/bin/uv

WORKDIR /app

COPY worker/ .

RUN uv sync --frozen

ENV PATH="/app/.venv/bin:${PATH}"

EXPOSE 8001

HEALTHCHECK --interval=10s --timeout=5s --start-period=10s --retries=5 \
    CMD wget -q -O /dev/null http://127.0.0.1:8001/health || exit 1

CMD ["uv", "run", "uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8001"]
