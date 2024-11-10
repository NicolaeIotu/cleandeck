# DevOps

1. **On the Development Machine**
    * ```composer create-project "cleandeck/cleandeck" ./```
    * Update file **.env.ini** (`baseURL`, `cookie[domain]` a.o.)
    * Update file **Application/public/robots.txt**
    * ```sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php apache2```, or  ```sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php nginx```
    * Start webserver
    * Start development
    * When development is completed run ```composer exec cleandeck-zip```. A zip archive will be created in the root
      directory of the project.

2. **On the Production Server**:
    * Upload zip archive generated on the development machine.
    * ```unzip -q {path_to_zip_archive}```
    * ```composer update```
    * ```composer dump-autoload```
    * ```sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php apache2```, or  ```sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php nginx```
    * Start webserver
    * Others

If archiving manually in preparation for production please note that the following directories are not required:

* build
* documentation
* tests
* tools
* vendor (if access to internet is available)
