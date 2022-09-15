# Projet SnowTricks P6

***

This Snowtricks project allows you to share tricks and comment on other tricks if you are registered.
Do not hesitate to enrich this site with images and videos of snowboarding

## A list of technologies used within the project

***

A list of technologies used within the project:
* PHP(https://www.php.net) Version 8.1.7
* Symfony(https://symfony.com) Version 6.1
* twig(https://twig.symfony.com) 3.0
* Composer(https://getcomposer.org) Version 2.3.7
* bootstap(https://getbootstrap.com) Version 5.1.3
* Fontawesome(https://fontawesome.com) Version 5.15.4

## installation guide

```shell
$ git clone https://github.com/ghislainvava/SnowTricks_OC.git
$ composer update
$ create an `.env` (from `.env.dist`) file and write the necessary information to the database
$ php bin/console doctrine:database:create
$ php bin/console doctrine:migrations:migrate
$ php bin/console doctrine:fixtures:load
$ php -S 127.0.0.1:8000 -t public public/index.php
```

