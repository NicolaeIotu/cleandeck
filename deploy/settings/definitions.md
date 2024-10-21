# Deploy Settings

These settings are used by utility **cleandeck-deploy**:
> sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy ...

When **cleandeck-deploy** runs, it will copy settings files from directory **deploy/settings** to destinations
as set in file **deploy/settings/definitions.json**.

During the transfer some strings will be converted at destination only as follows:
* **CLEANDECK_PUBLIC_PATH**: replaced by the path stored by the constant with the same name `CLEANDECK_PUBLIC_PATH`
* **CLEANDECK_SSL_CERTIFICATES_PATH**: replaced by the realpath to directory **deploy/ssl/generated**.
* **CLEANDECK_SERVER_NAME**: replaced by the server name retrieved from file **.env.ini** and adjusted (the scheme is eliminated).

The default settings are for Fedora like systems, so it is quite probable that you have to adjust paths to match
your OS/flavour, before running `sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy ...`.

**IMPORTANT!** Please submit your proposals for settings targeting other types of webservers.

## File definitions.json

Verify contents and adjust if required.

In sections **apache2**, **php**, the keys are paths to source files, while the values are paths
to target directories.<br>
Advanced users can add their own key / value pairs.

## Files in subdirectories of **deploy/settings**

Verify contents and adjust if required.

## Next

Run utility **cleandeck-deploy**:
* copy **PHP** settings files:
> sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php
* copy **PHP** and **apache2** settings files:
> sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php apache2
* copy **PHP** and modern **nginx** settings files:
> sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php nginx
* copy **PHP** and legacy **nginx** settings files:
> sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php nginx-ex
