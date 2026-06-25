.PHONY: help up down test lint analyse backend frontend worker

help:
	@echo "History AI — available targets:"
	@echo "  make up        Start all services (not configured yet)"
	@echo "  make down      Stop all services (not configured yet)"
	@echo "  make test      Run all test suites (not configured yet)"
	@echo "  make lint      Run all linters (not configured yet)"
	@echo "  make analyse   Run static analysis (not configured yet)"
	@echo "  make backend   Backend shell (not configured yet)"
	@echo "  make frontend  Frontend dev server (not configured yet)"
	@echo "  make worker    Worker shell (not configured yet)"

up:
	@echo "Not configured yet — Docker services will be added in a future task."
	@exit 1

down:
	@echo "Not configured yet — Docker services will be added in a future task."
	@exit 1

test:
	@echo "Not configured yet."
	@exit 1

lint:
	@echo "Not configured yet."
	@exit 1

analyse:
	@echo "Not configured yet."
	@exit 1

backend:
	@echo "Not configured yet."
	@exit 1

frontend:
	@echo "Not configured yet."
	@exit 1

worker:
	@echo "Not configured yet."
	@exit 1
