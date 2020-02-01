.PHONY: up down restart composer-install composer-update artisan-key-generate prepare-env-file prepare-dev-mysql-db artisan-migrate artisan-seed ssh phpunit test mysql

up:
	@rm -f laradock/.env ;\
	cp laradock/env-dev laradock/.env ;\
	docker-compose --file laradock/docker-compose.yml --project-directory laradock up -d workspace php-fpm nginx mysql ;\
	make composer-install ;\
	make prepare-env-file ;\
	make artisan-key-generate ;\
	make prepare-dev-mysql-db ;\
	make artisan-migrate ;\
	make artisan-seed ;\
	printf "\n=========> Server is available at: http://localhost\n"

down:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock down

restart:
	@make down ;\
	make up

composer-install:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace composer install

composer-update:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace composer update

artisan-key-generate:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace php artisan key:generate

prepare-env-file:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace rm -f .env
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace cp env-dev .env

prepare-dev-mysql-db:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace mysql -h mysql -u root -proot -e "drop database dev" > /dev/null
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace mysql -h mysql -u root -proot -e "create database dev" > /dev/null
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace mysql -h mysql -u root -proot -e "GRANT ALL PRIVILEGES ON *.* To 'dev'@'localhost' IDENTIFIED BY 'dev'" > /dev/null

artisan-migrate:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace php artisan migrate

artisan-seed:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace php artisan db:seed

artisan-telescope-clear:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace php artisan telescope:clear

ssh:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace bash

phpunit:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec workspace ./vendor/bin/phpunit

test:
	@make phpunit

mysql:
	@docker-compose --file laradock/docker-compose.yml --project-directory laradock exec mysql mysql -h mysql -u root -proot -D dev

	return 0;
