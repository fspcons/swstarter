# ============================================================
# So, I normally work on a MacBook but last week my display broke
# so the MacBook is at the shop and for the time being I had to 
# take the dust off my old Dell notebook and run an Ubuntu.
# Because of that, docker compose syntax is slightly different
# on Linux than on MacOS so I had to come up with this little
# AI hack to make sure it runs the compose commands correctly. 
# ============================================================

DOCKER_COMPOSE := $(shell \
	if command -v docker > /dev/null 2>&1 && docker compose version > /dev/null 2>&1; then \
		echo "docker compose"; \
	elif command -v docker-compose > /dev/null 2>&1; then \
		echo "docker-compose"; \
	else \
		echo "docker compose"; \
	fi \
)

.PHONY:  build up down restart logs test-be test-fe lint-be lint-fe clean run	

## Build all Docker images
build:
	$(DOCKER_COMPOSE) build

## Start all services in detached mode
up:
	$(DOCKER_COMPOSE) up -d
	@echo ""
	@echo "SWStarter is running at http://localhost:8080"
	@echo ""

## Stop all services
down:
	$(DOCKER_COMPOSE) down

## Restart all services
restart: down up

## Follow logs from all services
logs:
	$(DOCKER_COMPOSE) logs -f

## Run backend unit tests inside the container
test-be:
	$(DOCKER_COMPOSE) exec app php /var/www/backend/artisan test --testsuite=Unit

## Run frontend unit tests via Docker
test-fe:
	docker run --rm -v "$$(pwd)/frontend:/app" -w /app node:22-alpine sh -c "npm test -- --watchAll=false 2>&1 | grep -v 'npm notice'"

## Lint backend PHP code with Laravel Pint (--test = check only, no fixes)
lint-be:
	$(DOCKER_COMPOSE) exec app vendor/bin/pint --test -v

## Lint frontend TypeScript/React code with ESLint
lint-fe:
	docker run --rm -v "$$(pwd)/frontend:/app" -w /app node:22-alpine sh -c "npm run lint 2>&1 | grep -v 'npm notice'"

## Remove volumes and rebuild everything from scratch
clean:
	$(DOCKER_COMPOSE) down -v --remove-orphans
	$(DOCKER_COMPOSE) build --no-cache

run: build up
