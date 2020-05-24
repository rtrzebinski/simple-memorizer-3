.PHONY: build up down restart run start composer-install composer-update artisan-migrate artisan-seed artisan-telescope-clear mysql ssh test

build:
	@rm -f laradock/.env ;\
	cp laradock/env-dev laradock/.env ;\
	docker-compose --file laradock/docker-compose.yml --project-directory laradock build workspace php-fpm nginx mysql ;\

up:
	@rm -f laradock/.env ;\
	cp laradock/env-dev laradock/.env ;\
	docker-compose --file laradock/docker-compose.yml --project-directory laradock up -d workspace php-fpm nginx mysql ;\

down:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock down

restart:
	@make down ;\
	make up

run:
	@make composer-install ;\
	docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace rm -f .env ;\
	docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace cp env-dev .env ;\
	docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace php artisan key:generate ;\
	docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace mysql -h mysql -u root -proot -e "drop database dev" > /dev/null ;\
	docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace mysql -h mysql -u root -proot -e "create database dev" > /dev/null ;\
	docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace mysql -h mysql -u root -proot -e "GRANT ALL PRIVILEGES ON *.* To 'dev'@'localhost' IDENTIFIED BY 'dev'" > /dev/null ;\
	make artisan-migrate ;\
	make artisan-seed ;\
	printf "\n=========> Server is available at: http://localhost\n"

start:
	@make up ;\
	make run

composer-install:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace composer install

composer-update:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace composer update

composer-dump-autoload:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace composer dump-autoload

artisan-migrate:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace php artisan migrate

artisan-seed:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace php artisan db:seed

artisan-telescope-clear:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace php artisan telescope:clear

mysql:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec mysql mysql -h mysql -u root -proot -D dev

ssh:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace bash

args = `arg="$(filter-out $@,$(MAKECMDGOALS))" && echo $${arg:-${1}}`

test:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace ./vendor/bin/phpunit $(call args)

test-filter:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace ./vendor/bin/phpunit --filter $(call args)

paratest:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace ./vendor/bin/paratest --runner SqliteRunner
