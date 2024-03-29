## About

Simple memorizer is an educational tool using [spaced repetition](https://en.wikipedia.org/wiki/Spaced_repetition) technique that is usually performed with flashcards. Newly introduced and more difficult exercises are shown more frequently, while older and less difficult are shown less frequently in order to exploit the psychological [spacing effect](https://en.wikipedia.org/wiki/Spacing_effect). The use of spaced repetition has been [proven to increase the rate of learning](https://www.ncbi.nlm.nih.gov/pmc/articles/PMC5126970/).

Although the principle is useful in many contexts, spaced repetition is commonly applied when a learner must acquire many items and retain them indefinitely in memory. It is, therefore, well suited for the problem of vocabulary acquisition in the course of **second-language learning**.

[Documentation](https://github.com/rtrzebinski/simple-memorizer-3/wiki) | [Kanban board](https://github.com/rtrzebinski/simple-memorizer-3/projects/1) | [Live demo](https://peaceful-chamber-70920.herokuapp.com)

[![Build Status](https://travis-ci.com/rtrzebinski/simple-memorizer-3.svg?branch=master)](https://travis-ci.com/rtrzebinski/simple-memorizer-3)

## Description

During daily sessions a user is presented a series of exercises and provides feedback whether he knows the answer or not. This data is being collected making the tool more adequate the more it is used. Application algorithms analyse multiple factors like ratio between good and bad answers, number of answers on a given day or order in which these are given.

User interface is **minimalistic and optimised to run very fast**. Providing feedback only requires pressing either the 'Good answer' or 'Bad answer' button. Doing so is concluded with the next exercise being loaded, making the process smooth and easy to follow. Another advantage of this approach is ease of use on mobile devices without a need to type an answer in. It also increases the number of exercises served in a time allowing it to be useful even during shorter sessions.

New features and improvements are actively being added to the system. The goal of the project is creating an **efficient and user friendly application that is easy to use and effective**.

## Implementation details

- Web application is developed with [PHP](http://php.net) and [Laravel](https://laravel.com).

- Frontend interface is based on [Bootstrap](http://getbootstrap.com). Views are generated by PHP. A separated frontend app is not used at the moment. [jQuery](https://jquery.com) is utilised to add dynamic UI elements.

- [MySQL](https://mysql.com) database serves as a data store.

- App is covered by automated tests written with [PHPUnit](https://phpunit.de) framework. Tests are set up to run using in memory [SQLite](https://www.sqlite.org/) database, which significantly speeds up testing process.

### Desktop screenshots 

<img src="/images/screenshot_web_1.png" width="200" /> <img src="/images/screenshot_web_2.png" width="200" /> <img src="/images/screenshot_web_3.png" width="200" />

### Mobile screenshots

<img src="/images/screenshot_iphone_1.PNG" width="200" /> <img src="/images/screenshot_iphone_2.PNG" width="200" /> <img src="/images/screenshot_iphone_3.PNG" width="200" /> <img src="/images/screenshot_iphone_4.PNG" width="200" />

