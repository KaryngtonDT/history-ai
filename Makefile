.PHONY: help install up down ps logs config \
	dev dev-stop prod prod-stop prod-rebuild prod-fresh prod-restart \
	prod-backend prod-frontend prod-worker prod-migrate prod-prune-backend-run prod-logs \
	test test-backend test-frontend test-worker test-all ci \
	backup restore verify-backup health doctor status migrate shell-backend

COMPOSE ?= docker compose
COMPOSE_DEV := $(COMPOSE)
COMPOSE_PROD := $(COMPOSE) -f docker-compose.prod-like.yml
BACKEND_EXEC := $(COMPOSE_PROD) exec backend
FRONTEND_EXEC := $(COMPOSE_PROD) exec frontend
WORKER_EXEC := $(COMPOSE_PROD) exec worker

help:
	@echo "========================================================="
	@echo "                LUMEN COMMAND CENTER"
	@echo "========================================================="
	@echo ""
	@echo "INSTALLATION"
	@echo "  make install          First-time setup (storage tree + up)"
	@echo "  make up               Start dev stack"
	@echo "  make down             Stop dev stack"
	@echo ""
	@echo "PRODUCTION-LIKE"
	@echo "  make prod             Start prod-like stack"
	@echo "  make prod-stop        Stop prod-like stack"
	@echo "  make prod-rebuild     Rebuild all prod-like images"
	@echo "  make prod-restart     Restart prod-like stack"
	@echo "  make prod-fresh       DESTROY volumes + reset (confirmation)"
	@echo ""
	@echo "SERVICES"
	@echo "  make prod-backend     Rebuild backend only"
	@echo "  make prod-frontend    Rebuild frontend only"
	@echo "  make prod-worker      Rebuild worker only"
	@echo "  make prod-migrate     Run Doctrine migrations"
	@echo "  make prod-logs        Follow prod-like logs"
	@echo ""
	@echo "TESTS"
	@echo "  make test-all         Full platform test suite"
	@echo "  make test-backend     PHPUnit (backend)"
	@echo "  make test-frontend    Frontend build + tests"
	@echo "  make test-worker      Worker pytest + ruff"
	@echo ""
	@echo "BACKUP"
	@echo "  make backup           Create full backup"
	@echo "  make restore          Restore from backups/latest"
	@echo "  make verify-backup    Verify backup integrity"
	@echo ""
	@echo "SYSTEM"
	@echo "  make health           Curl /health /ready /live"
	@echo "  make doctor           Full platform diagnostic"
	@echo "  make status           Docker service status"
	@echo "========================================================="

install:
	@test -f .env || cp .env.example .env
	@$(MAKE) storage-tree
	@$(MAKE) up

storage-tree:
	@mkdir -p storage/uploads/video storage/uploads/audio storage/uploads/pdf \
		storage/artifacts/transcript storage/artifacts/translation storage/artifacts/audio \
		storage/artifacts/voiceclone storage/artifacts/lipsync storage/artifacts/render storage/artifacts/quality \
		storage/shadow/identity storage/shadow/sessions storage/shadow/session-learning storage/shadow/relationship storage/shadow/memory storage/shadow/teaching storage/shadow/knowledge storage/shadow/goals storage/shadow/mentor storage/shadow/executive storage/shadow/brain storage/shadow/presence storage/learning storage/workspace \
		storage/logs storage/temp storage/cache
	@mkdir -p models/whisper models/ollama models/f5 models/openvoice models/latentsync
	@mkdir -p backups/mysql backups/storage backups/configuration

up:
	$(COMPOSE_DEV) up -d --build

down:
	$(COMPOSE_DEV) down

ps:
	$(COMPOSE_DEV) ps

logs:
	$(COMPOSE_DEV) logs -f

config:
	$(COMPOSE_DEV) config

status:
	$(COMPOSE_PROD) ps

dev: up

dev-stop: down

prod:
	$(COMPOSE_PROD) up -d --build

prod-stop:
	$(COMPOSE_PROD) down

prod-rebuild:
	$(COMPOSE_PROD) up -d --build --force-recreate

prod-restart:
	$(COMPOSE_PROD) restart

prod-fresh:
	@echo "ATTENTION: This will DELETE all Docker volumes (postgres, redis, minio)."
	@echo "Storage bind mount (./storage) is NOT deleted."
	@echo "Type exactly: DELETE EVERYTHING"
	@read -r confirm && [ "$$confirm" = "DELETE EVERYTHING" ] || (echo "Aborted." && exit 1)
	$(COMPOSE_PROD) down -v

prod-backend:
	$(COMPOSE_PROD) up -d --build backend

prod-frontend:
	$(COMPOSE_PROD) up -d --build frontend

prod-worker:
	$(COMPOSE_PROD) up -d --build worker

prod-prune-backend-run:
	@ids=$$(docker ps -aq --filter "name=history-ai-backend-run-"); \
	if [ -n "$$ids" ]; then docker rm -f $$ids; fi

prod-migrate: prod-prune-backend-run
	$(COMPOSE_PROD) exec -T backend php bin/console doctrine:migrations:migrate --no-interaction

prod-logs:
	$(COMPOSE_PROD) logs -f

migrate: prod-migrate

shell-backend: prod-prune-backend-run
	$(BACKEND_EXEC) sh

test-backend: prod-prune-backend-run
	$(BACKEND_EXEC) php bin/phpunit

test-frontend:
	$(FRONTEND_EXEC) npm run build
	$(FRONTEND_EXEC) npm test -- --run

test-worker:
	$(WORKER_EXEC) pytest
	$(WORKER_EXEC) ruff check .

test-all: test-backend test-frontend test-worker

test: test-all

ci: prod test-all health

backup:
	bash scripts/backup.sh

restore:
	bash scripts/restore.sh

verify-backup:
	bash scripts/verify-backup.sh

health:
	curl -sf http://localhost:8000/health
	@echo ""
	curl -sf http://localhost:8000/ready
	@echo ""
	curl -sf http://localhost:8000/live
	@echo ""

doctor:
	bash scripts/doctor.sh
