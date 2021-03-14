#!/bin/bash
# Start application in macOS
# See https://github.com/rtrzebinski/simple-memorizer-3/wiki

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
