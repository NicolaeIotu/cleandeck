# Setup Guide

## Prepare development machine

### Install Composer (https://getcomposer.org):

> php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

- Perform checksum verification as listed on the official website https://getcomposer.org.

> php composer-setup.php
> php -r "unlink('composer-setup.php');"

- File composer.phar is now located in current working directory.
- Make composer available globally:

> sudo mv composer.phar /usr/local/bin/composer

### Create a new CleanDeck project

> composer create-project "cleandeck/cleandeck" ./

### Adjust the main settings of the application

Adjust the main settings of the application in file **.env.ini**.

Some settings are used by tool **cleandeck-deploy**, so it's important to adjust at least the following:
* `baseURL`
* `cookie[domain]`


### Adjust robots.txt file

Adjust file **Application/public/robots.txt**.

### Set up the HTTP server and PHP

> sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php apache2

, or

> sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php nginx

### Start Http server

> sudo systemctl start httpd

, or

> sudo systemctl restart httpd

## Prepare and start CMD-Auth machine

Download the community edition of CMD-Auth (or any other version of CMD-Auth).
See [https://link133.com](https://link133.com) for download options, settings, instructions, support and more.

Install CMD-Auth, note the access point and use it to update the file **.env.ini** on the development machine.
For example:
> authURL='https://192.168.xx.xx:12345'

## Develop your own application (upstream machine)

* [Development](./Development.md "Development")
* [DevOps](./DevOps.md "DevOps")
* [Production](./Production.md "Production")
* [Routes](./Routes.md "Routes")
* [SEO](./SEO.md "SEO")
* [Sitemap](./Sitemap.md "Sitemap")

## Troubleshooting

On the server hosting CleanDeck application investigate direct error messages, Http logs, PHP logs, system logs.

On the server hosting CMD-Auth investigate application logs (as set) and system logs.

