.PHONY: help ps build build-prod start fresh fresh-prod stop restart destroy \
	cache cache-clear migrate migrate migrate-fresh tests tests-html

CONTAINER_PHP=job-applying-laravel.test-1
CONTAINER_REDIS=job-applying-redis-1
#CONTAINER_DATABASE=job-applying-pgsql-1
CONTAINER_DATABASE=job-applying-mysql-1

help: ## Print help.
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n\nTargets:\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

a-sail: ## Configuring A Shell Alias
	@alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'

start: ## Start all containers
#	@docker compose up --force-recreate -d
	@./vendor/bin/sail up -d

fresh:  ## Destroy & recreate all using dev containers.
	make stop
	make destroy
	make build
	make start

stop: ## Stop all containers
	@#docker compose stop
	@./vendor/bin/sail stop

down: ## Remove all containers
	@./vendor/bin/sail down

restart: stop start ## Restart all containers

destroy: down ## Destroy all containers

npm-i: ## Install npm dependents
	@./vendor/bin/sail npm install

npm-b: ## Build front
	@./vendor/bin/sail npm run build

dev: ## Frontend dev
	@./vendor/bin/sail npm run dev

ssh: ## SSH into PHP container
	docker exec -it ${CONTAINER_PHP} sh

db: ## Run into DB container
	docker exec -it ${CONTAINER_DATABASE} bash

install: ## Run composer install
	@./vendor/bin/sail composer install
	#docker exec ${CONTAINER_PHP} composer install

migrate: ## Run migration files
	@./vendor/bin/sail artisan migrate
	#docker exec ${CONTAINER_PHP} php artisan migrate

migrate-fresh: ## Clear database and run all migrations
	@./vendor/bin/sail artisan migrate:fresh
	#docker exec ${CONTAINER_PHP} php artisan migrate:fresh

seed-fresh: ## Refresh Database fake data
	@./vendor/bin/sail artisan migrate:fresh --seed

tests: ## Run all tests
	@./vendor/bin/sail artisan test
	#docker exec ${CONTAINER_PHP} ./vendor/bin/phpunit

tests-html: ## Run tests and generate coverage. Report found in reports/index.html
	#docker exec ${CONTAINER_PHP} php -d zend_extension=xdebug.so -d xdebug.mode=coverage ./vendor/bin/phpunit --coverage-html reports

am: ## Make something with artisan - make am c="model"
	@./vendor/bin/sail artisan make:$(c)

amc: ## Make controller with artisan
	@./vendor/bin/sail artisan make:controller

amcr: ## Make controller as resource with artisan
	@./vendor/bin/sail artisan make:controller -r --model=$(c)

amm: ## Make model with artisan
	@./vendor/bin/sail artisan make:model

amma: ## Make model with artisan
	@./vendor/bin/sail artisan make:model --all

ammg: ## Make migration with artisan
	@./vendor/bin/sail artisan make:migration

amrs: ## Make resource with artisan
	@./vendor/bin/sail artisan make:resource

amrq: ## Make request with artisan
	@./vendor/bin/sail artisan make:request
