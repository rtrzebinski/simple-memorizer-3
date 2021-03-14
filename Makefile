SHELL := /bin/bash
default: help

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

start: ## create, migrate and seed db; install dependencies; run application
	mysql -uroot -e "DROP DATABASE IF EXISTS dev;"
	mysql -uroot -e "CREATE DATABASE dev;"
	mysql -uroot -e "DROP USER IF EXISTS dev;"
	mysql -uroot -e "CREATE USER dev IDENTIFIED BY 'dev';"
	mysql -uroot -e "GRANT ALL ON *.* TO dev;"
	composer install --prefer-dist --optimize-autoloader --no-interaction
	cp .env.dev .env
	php artisan migrate
	php artisan db:seed
	php artisan serve

test: ## run tests in parallel
	php artisan test --parallel
