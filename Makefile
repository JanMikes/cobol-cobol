.DEFAULT_GOAL := help

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

setup: ## Initial setup of the application
	docker compose up -d
	docker compose exec web composer install
	docker compose exec web bin/console doctrine:database:create --if-not-exists
	docker compose exec web bin/console doctrine:migrations:migrate --no-interaction
	docker compose exec web bin/console doctrine:fixtures:load --no-interaction

up: ## Start all containers
	docker compose up -d

down: ## Stop all containers
	docker compose down

build: ## Build all containers
	docker compose build

restart: ## Restart all containers
	docker compose restart

logs: ## Show container logs
	docker compose logs -f

shell: ## Access web container shell
	docker compose exec web sh

console: ## Access Symfony console
	docker compose exec web bin/console

migrate: ## Run database migrations
	docker compose exec web bin/console doctrine:migrations:migrate

fixtures: ## Load database fixtures
	docker compose exec web bin/console doctrine:fixtures:load --no-interaction

cache-clear: ## Clear Symfony cache
	docker compose exec web bin/console cache:clear

phpstan: ## Run PHPStan analysis
	docker compose exec web vendor/bin/phpstan analyse

test: ## Run tests
	docker compose exec web bin/phpunit

install: ## Install composer dependencies
	docker compose exec web composer install

update: ## Update composer dependencies
	docker compose exec web composer update

db-create: ## Create database
	docker compose exec web bin/console doctrine:database:create

db-drop: ## Drop database
	docker compose exec web bin/console doctrine:database:drop --force

schema-update: ## Update database schema
	docker compose exec web bin/console doctrine:schema:update --force

mailpit: ## Open Mailpit interface
	@echo "Opening Mailpit at http://localhost:8025"
	@open http://localhost:8025 2>/dev/null || echo "Open http://localhost:8025 in your browser"

app: ## Open application
	@echo "Opening application at http://localhost:8080"
	@open http://localhost:8080 2>/dev/null || echo "Open http://localhost:8080 in your browser"