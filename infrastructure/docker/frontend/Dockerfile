# React frontend — Node 22, reproducible build via Docker.
# Build context: repository root.

FROM node:22-alpine

WORKDIR /app

RUN apk add --no-cache wget

COPY frontend/package.json frontend/package-lock.json ./

RUN npm ci

COPY frontend/ .

RUN npm run build

EXPOSE 5173

HEALTHCHECK --interval=10s --timeout=5s --start-period=10s --retries=5 \
    CMD wget -q --spider http://127.0.0.1:5173/ || exit 1

CMD ["npm", "run", "preview", "--", "--host", "0.0.0.0", "--port", "5173"]
