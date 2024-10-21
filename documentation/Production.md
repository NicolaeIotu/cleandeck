# Using in Production

After the development is completed is time to go live.

Make sure you observe [DevOps](./DevOps.md "DevOps") instructions.

## CleanDeck Settings

File **.env.ini**:

* **Mandatory**
  * ```ENVIRONMENT = production```, or ```ENVIRONMENT = staging``` when staging
  * ```baseURL = https://application.address```
  * ```authURL = https://cmd-auth.address:port```
  * ```CONTACT_EMAIL = real@contact.email```
  * ```aws_ses```
  * ```aws_s3```
* **Optional adjustments**
  * ```oauth2_google (highly recommeded)```
  * ```aws_sqs (for professionals)```
* others as required

## Other Important Settings
Adjust contents of file **Application/public/robots.txt**.

## Deployment Aids

CleanDeck requires advanced settings for webservers, so it's highly recommended
to use tool **cleandeck-deploy** in order to set up PHP and/or webservers Apache2/Nginx (at the moment).<br>
Follow instructions regarding tool [cleandeck-deploy](./Tools.md#cleandeck-deploy "cleandeck-deploy")

For example:
> sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php apache2

, or
> sudo env COMPOSER_ALLOW_SUPERUSER=1 composer exec cleandeck-deploy php nginx

Restart webserver on completion.

As usual don't forget to run:
> composer update
> composer dump-autoload

## CMD-Auth Settings

Make sure that at least the default administration password is changed. You could also change the default
administration account and/or decrease the rank of the default administration account.

For more see CMD-Auth documentation i.e. https://localhost:12345/documentation and ```man cmd-auth```.

## Shutting Down

In order to shut down the server gracefully, the machine should not serve requests i.e. unregister with the
load-balancer, stop httpd/nginx etc.

Pending file operations must be completed. Pending emails which are stored locally should be delivered.<br>
These pending operations are stored in database files **Application/writable/database/file-ops-queue.sqlite** and
 **Application/writable/database/emails-queue.sqlite**.

Tool **cleandeck-process-queues** will process pending operations:
> composer exec cleandeck-process-queues
