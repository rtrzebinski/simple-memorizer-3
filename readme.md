## About

Simple memorizer helps efficiently memorizing any question-answer sets.

[Application live demo](https://simple-memorizer.online)

[![Build Status](https://travis-ci.com/rtrzebinski/simple-memorizer-3.svg?branch=master)](https://travis-ci.com/rtrzebinski/simple-memorizer-3)

### How does it work

- User can subscribe and learn one of lessons created by other users or create own lesson.

- Lessons created by user might be public (can be used by other users) or private (visible for lesson creator only).

- Each lesson contains number of exercises. Exercises has question and answer.

- While learning, user is asked if he knows the answer to a question. User might gives app the feedback simply pressing one of 2 buttons - if he knows the answer or not. App stores this information and uses it to adjust order of questions being asked.

- Generally questions that user knows less are served more often and questions that user knows better are served less often.

- App engine is analysing previous answers of a user, and serves next questions is optimal order, to ensure most efficient learning process.

### Implementation details

- Application is developed with [PHP 7](http://php.net) and [Laravel framework](https://laravel.com).

- Frontend interface is based on [Bootstrap 3](http://getbootstrap.com). Views are generated by PHP, separated frontend app is not used at the moment. [jQuery](https://jquery.com) is utilised to add dynamic UI elements.

- [MySQL](https://mysql.com) database serves as data store

- App is covered by automated tests written with [PHPUnit](https://phpunit.de) framework. Tests are set up to run using in memory [SQLite](sqlite) database, which significantly speeds up testing process.

### REST API

Application provides WEB interface as well as REST API, which allows integration with external client apps.

[REST API documentation](https://github.com/rtrzebinski/simple-memorizer-3/wiki/REST-API)

## Docker support

Thanks to [Laradock](https://laradock.io) project has a built in [Docker](https://www.docker.com) support. If you have [Docker](https://www.docker.com) installed you can **easily run and develop application locally** using few simple commands listed below.

### Prerequisites

- [docker](https://www.docker.com/)
- [docker-compose](https://docs.docker.com/compose/)
- [make](https://www.gnu.org/software/make/)
- `sh`

### Commands

- `make build` - build or rebuild docker services
- `make up` - start docker services
- `make down` - stop docker services
- `make restart` - restart docker services
- `make run` - prepare and run application
- `make start` - start docker services, prepare and run application
- `make composer-install` - install composer dependencies
- `make composer-update` - update composer dependencies
- `make artisan-migrate` - run all migrations
- `make artisan-seed` - seed the database with records
- `make artisan-telescope-clear` - clear all entries from telescope
- `make mysql` - connect to dev database via mysql cli client
- `make ssh` - ssh to workspace container
- `make test` - run phpunit test suite
- `make test-filter` - run phpunit filtered by class or test name
- `make paratest` - run phpunit paratest suite (8 processed in parallel)
