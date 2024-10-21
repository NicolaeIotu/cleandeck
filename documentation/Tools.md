# Tools

* [cleandeck-routes](#cleandeck-routes)
* [cleandeck-clear-cache](#cleandeck-clear-cache)
* [cleandeck-generate-app-key](#cleandeck-generate-app-key)
* [cleandeck-generate-ssl](#cleandeck-generate-ssl)
* [cleandeck-unzip](#cleandeck-unzip)
* [cleandeck-zip](#cleandeck-zip)
* [cleandeck-deploy](#cleandeck-deploy)
* [cleandeck-process-queues](#cleandeck-process-queues)

## cleandeck-routes

Shows a list of active routes and relevant details: path, controller and method.

Options:

* list **GET** routes:

> composer exec cleandeck-routes **GET**

* list **POST** routes:

> composer exec cleandeck-routes **POST**

* list **CLI** routes:

> composer exec cleandeck-routes **CLI**

* search **keyword** in path and list matching routes:

> composer exec cleandeck-routes **keyword**

## cleandeck-clear-cache

Clear HTTP and Throttle cache as set in file **.env.ini**.

> composer exec cleandeck-clear-cache

## cleandeck-generate-app-key

Generates a new **app_key** for your application in file **.env.ini**.

A new **app_key** is automatically generated when a project is created.

> composer exec cleandeck-generate-app-key

## cleandeck-generate-ssl

Generates:
* optional, a new SSL password in files **deploy/ssl/ssl-settings.ini** and
  **deploy/ssl/generated/cleandeck-ssl-password.txt**,
* new self-signed SSL certificate files in directory **deploy/ssl/generated**

Generate new SSL password and new self-signed SSL certificate:
> composer exec cleandeck-generate-ssl

Generate new self-signed SSL certificate with no password (useful for **lighttpd** and others):
> composer exec cleandeck-generate-ssl no-password

Generate new self-signed SSL certificate using the password given in file **deploy/ssl/ssl-settings.ini**:
> composer exec cleandeck-generate-ssl own-password

If not previously done, run [cleandeck-deploy](#cleandeck-deploy) after executing **cleandeck-generate-ssl**.

## cleandeck-unzip

Extract directory **Application/** and file **.env.ini** from a zip archive to a target directory.<br>
The operation will overwrite existing files so make sure to back up data if required before running this command.<br>
This utility should be used for example when you want to continue working on your application on a freshly installed
CleanDeck project.<br>
While it may be working with archives created using other means, it is recommended to use archives created using own
tool **cleandeck-zip** or otherwise created in a similar fashion.

At the moment other directories/files must be extracted manually.

> composer exec cleandeck-unzip {path_to_archive} {destination_directory}

## cleandeck-zip

Creates a zip archive using only the content required in production. Upload this archive and unzip on production servers
using external tools such as **unzip**. Own tool **cleandeck-unzip** can be used in order extract the contents
required in order to continue working with the project in another location, possibly a fresh installation of CleanDeck.

Use own tool [cleandeck-deploy](#cleandeck-deploy) in order to set up PHP and webservers.

> composer exec cleandeck-zip

Options:

* tag - Alphanumeric-dash (a-zA-Z0-9-) string which will be included in the final name of the archive.

> composer exec cleandeck-zip my-tag

## cleandeck-deploy

Copy files with custom settings for PHP and webservers (Apache2 or Nginx at the moment) from directory
**deploy/settings** to target OS paths.<br>
Settings (sources and destinations) can be adjusted in file **deploy/settings/definitions.json**.
File **.env.ini** **must** be updated before running *cleandeck-deploy*.

The defaults used in file **deploy/settings/definitions.json** are for Fedora like systems. Adjust if required. <br>
Also don't forget to adjust if required the contents of source files in subdirectories of **deploy/settings**.

Most settings are sensitive.

Examples:

* copy **PHP** settings files:
> sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php
* copy **PHP** and **apache2** settings files:
> sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php apache2
* copy **PHP** and modern **nginx** settings files:
> sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php nginx
* copy **PHP** and legacy **nginx** settings files:
> sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php nginx-ex

On completion restart target webserver, php-fpm.service and/or other units in order to apply fresh settings.

## cleandeck-process-queues

Process pending file operations and email operations.<br>
This tool must be used before shutting down the webserver and anytime when required to process pending file operations
and/or email operations. Note that file operations and email operations are processed immediately as they occur,
but in case the operations fail then these failed operations will be queued. This tool processed the queues of
pending operations including previously failed operations.
> composer exec cleandeck-process-queues
