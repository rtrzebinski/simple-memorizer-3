SHELL := /bin/bash
default: help

args = `arg="$(filter-out $@,$(MAKECMDGOALS))" && echo $${arg:-${1}}`
services = workspace php-fpm nginx mysql redis

help: ## Show this help
	@IFS=$$'\n' ; \
    help_lines=(`fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##/:/'`); \
    printf "%-30s %s\n" "target" "help" ; \
    printf "%-30s %s\n" "------" "----" ; \
    for help_line in $${help_lines[@]}; do \
        IFS=$$':' ; \
        help_split=($$help_line) ; \
        help_command=`echo $${help_split[0]} | sed -e 's/^ *//' -e 's/ *$$//'` ; \
        help_info=`echo $${help_split[2]} | sed -e 's/^ *//' -e 's/ *$$//'` ; \
        printf '\033[36m'; \
        printf "%-30s %s" $$help_command ; \
        printf '\033[0m'; \
        printf "%s\n" $$help_info; \
    done

clean: ## Stop all running docker containers (recommended to run before 'start' command to ensure ports are not taken)
	@echo 'Stopping all running containers...'
	@docker ps -q | xargs docker stop 1>/dev/null

start: ## Create and start containers, composer dependencies, db migrate and seed etc. - everything in one command
	@if [ ! -e ".env" ]; then cp .env.example .env; fi
	@make up
	@make composer-install
	@make db-fresh
	@printf "\n=========> Server is available at: http://localhost\n"

build: ## Build or re-build containers
	rm -rf laradock
	git clone git@github.com:rtrzebinski/laradock.git
	cp .laradock.env.example laradock/.env
	docker-compose --file laradock/docker-compose.yml --project-directory laradock build $(services)

up: ## Start containers
	@echo 'Starting application containers...'
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock up -d $(services)

down: ## Stop and remove containers, networks, images, and volumes
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock down

composer-install: ## Composer install
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec -T workspace composer install

composer-update: ## Composer update
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec -T workspace composer update

db-create: ## Create dev mysql database
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace mysql -h mysql -u root -proot -e "DROP DATABASE IF EXISTS dev;" 1>/dev/null
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace mysql -h mysql -u root -proot -e "CREATE DATABASE dev;" 1>/dev/null
	@echo 'Database "dev" created.'
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace mysql -h mysql -u root -proot -e "DROP USER IF EXISTS dev;" 1>/dev/null
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace mysql -h mysql -u root -proot -e "CREATE USER dev IDENTIFIED BY 'dev';" 1>/dev/null
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace mysql -h mysql -u root -proot -e "GRANT ALL ON *.* TO dev;" 1>/dev/null
	@echo 'Database user "dev" created with password "dev".'

db-migrate: ## Migrate dev mysql database
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace php artisan migrate

db-seed: ## Seed dev mysql database
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace php artisan db:seed

db-fresh: ## Create, migrate and seed mysql database
	@make db-create
	@make db-migrate
	@make db-seed

bash: ## SSH workspace container (run bash)
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace bash

test: ## Run all unit tests or given test file
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace ./vendor/bin/phpunit $(call args)

test-filter: ## Run unit tests of given class or test method
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace ./vendor/bin/phpunit --filter $(call args)

paratest: ## Run test with paratest
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace ./vendor/bin/paratest --runner SqliteRunner