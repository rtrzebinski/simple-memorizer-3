Simple memorizer is a lightweight web application that helps to efficiently memorize any question-answer sets. This is third rewrite of the app, it was internally redesigned and implemented with [Laravel 5](https://laravel.com) and [PHP 7](http://php.net).

Application consists of web interface, and REST API which allows intergation with external client apps.

[REST API documentation](https://github.com/rtrzebinski/simple-memorizer-3/wiki/REST-API)

[Application live demo](https://simple-memorizer.online)

### How does it work

- User can subscribe and learn one of lessons created by other users, or create own lesson. Lessons created by user might be public (these might be used by other users) or private (these are visible for lesson creator only).

- Each lesson contains number of exercises. Exercises has question and answer.

- In learning mode application asks user if he knows the answer to question. User gives app feedback - if he knows the answer, or not. App stores this information and uses it to adjust order of questions being asked.

- Questions that user knows less are served more often, questions that user knows better are server less othwen. This way user can efficiently memorize the entire lesson.

### Technical details

- Application is developed using [Laravel 5](https://laravel.com) and [PHP 7](http://php.net).

- Front end interface is based on [Bootstrap 3](http://getbootstrap.com).

- App is covered by automated tests written with [PHPUnit](https://phpunit.de) framework. Test are set up to run using in memory [SQLite](sqlite) database, which significantly speeds up testing process.
