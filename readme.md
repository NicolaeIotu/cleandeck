# CleanDeck for CMD-Auth

Quick development and deployment for mega-scale, fully distributed REST applications
using awesome PHP and CMD-Auth of [https://link133.com](https://link133.com).

## Quick Start

* ```composer create-project "cleandeck/cleandeck" ./```
* ```composer update```
* ```composer dump-autoload```
* update file **.env.ini**
* update file **Application/public/robots.txt**
* if required adjust contents in directory **deploy/settings** (default settings are for Fedora and others alike)
* setup PHP and webserver (choose a version from the list below):
  * ```sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php apache2```
  * ```sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php nginx```
  * ```sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php nginx-ex```
* (re)start webserver and PHP-FPM after each use of command `composer exec cleandeck-deploy`
* start development


CleanDeck has a really simple top-level structure.<br>
All application contents is placed inside directory **Application** (including **public** HTTP directory).
You should only use directory **Application** when developing an application.

For changes outside directory **Application** you should probably [start contributing](./documentation/Contributing.md "Contributing").

Framework stuff is placed inside directory **Framework**.


## Download project

This project may be downloaded from:

* https://packagist.org - ```composer create-project cleandeck/cleandeck ./```
* [GitHub](https://github.com/NicolaeIotu/cleandeck "CleanDeck on GitHub")

## Documentation

* [About](./documentation/about.html "About")
* [Contributing](./documentation/Contributing.md "Contributing")
* [Development](./documentation/Development.md "Development")
* [DevOps](./documentation/DevOps.md "DevOps")
* [Middleware](./documentation/Middleware.md "Middleware")
* [Production](./documentation/Production.md "Production")
* [Routes](./documentation/Routes.md "Routes")
* [SEO](./documentation/SEO.md "SEO")
* [Setup Guide](./documentation/Setup-Guide.md "Setup Guide")
* [Sitemap](./documentation/Sitemap.md "Sitemap")
* [Views](./documentation/Views.md "Views")
