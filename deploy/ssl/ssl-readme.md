# Directory deploy/ssl

## deploy/ssl/ssl-settings.ini
Only modify the contents of file **deploy/ssl/ssl-settings.ini**.<br>
The password in file **deploy/ssl/ssl-settings.ini** is modified automatically:
* when creating a new project
* when running `composer exec cleandeck-generate-ssl`
* when running method `Framework\Support\Scripts\ComposerScripts::generateSslPassword`


## deploy/ssl/scripts
Very sensitive scripts used by **apache2** directive **SSLPassPhraseDialog** a.o.<br>
Under most circumstances you should not alter the contents. Expert intervention only.

## deploy/ssl/generated
This directory will store self-signed ssl certificate files.<br>
Under most circumstances you should not alter the contents. The files in this directory will be used by your
SSL webserver.
