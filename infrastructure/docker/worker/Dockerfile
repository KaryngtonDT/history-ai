# Placeholder — replaced when the Python worker is installed (TASK-0005).
FROM nginx:1.27-alpine

HEALTHCHECK --interval=10s --timeout=5s --start-period=5s --retries=3 \
  CMD wget -q --spider http://127.0.0.1/ || exit 1
