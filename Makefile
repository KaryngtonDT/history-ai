.PHONY: help up down ps logs config test lint analyse backend frontend worker

help:
	@echo "History AI — available targets:"
	@echo "  make up        Start all Docker services"
	@echo "  make down      Stop all Docker services"
	@echo "  make ps        Show service status"
	@echo "  make logs      Follow service logs"
	@echo "  make config    Validate docker-compose.yml"
	@echo "  make test      Run all test suites (not configured yet)"
	@echo "  make lint      Run all linters (not configured yet)"
	@echo "  make analyse   Run static analysis (not configured yet)"
	@echo "  make backend   Backend shell (not configured yet)"
	@echo "  make frontend  Frontend dev server (not configured yet)"
	@echo "  make worker    Worker shell (not configured yet)"

up:
	docker compose up -d --build

down:
	docker compose down

ps:
	docker compose ps

logs:
	docker compose logs -f

config:
	docker compose config

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
