# Middleware - HttpCaching::class

* don't use it for frequently changing content such as search results a.o. (or keep caching interval short)
* don't use it for private downloads
* don't use it for critical/sensitive content (unless encrypted)
* use the private cache only when mandatory
* administration routes (/admin/...) must use "HttpCaching::class => ['private' => true],"
* user private routes must use "HttpCaching::class => ['private' => true]," (user must be authenticated)
* setting "HttpCaching::class => ['private' => true]," can only be used with UserDetails::class having
  the setting for 'required_auth' (the first element of the array)
  set to *true* i.e. "UserDetails::class => [true, '/login'],"


**HttpCaching::class** settings [array]:
* *private* [boolean=false] (optional, available for GET routes): private cache, cache available
  to a single authenticated user
* *interval* [integer] (optional, available for GET routes): cache interval measured in seconds;
  by default 1800 seconds for private cache, and 10800 seconds for public cache
* *tags* [string[]] (optional, available for GET routes): an array of tags which may be used later with
  setting *clear-private-tags*, or *clear-public-tags* in order to delete some entries
* *clear-private-urls* [string[]] (optional, available for POST routes): an array of endpoints which will be cleared
  from the private cache of the user when this route is used
* *clear-public-urls* [string[]] (optional, available for POST routes): an array of endpoints which will be cleared
  from the public cache when this route is used
* *clear-private-tags* [string[]] (optional, available for POST routes): an array of tags; deletes cached private endpoints
  tagged using any of the tags indicated here.
* *clear-public-tags* [string[]] (optional, available for POST routes): an array of tags; deletes cached public endpoints
  tagged using any of the tags indicated here.


Custom tags are added using setting *tags*.

**Framework tags are automatically added**:
* **$UID** - Use this tag to delete all cached private endpoints belonging to a certain user (i.e. used by endpoint */logout*).
  Usable only with setting *clear-private-tags*.
* the encoded path of the endpoint - This tag may be used in order to delete parametric endpoint a.o. See example below.

#### Automatic Removal of a Parametric Endpoint From the Cache

In order to remove privately cached endpoint **/support-cases/case/details/(:segment)** use the following syntax:

```php
// File POSTRoutes.php.
$routes->add(
    '/support-cases/case/close',
    SupportCasesLifecycleController::class,
    'remote_request_case_close',
    [
        Throttle::class,
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [true, '/login'],
        HttpCaching::class => [
            'clear-private-urls' => [
                '/user',
                '/support-cases',
            ],
            'clear-private-tags' => [
                '/support-cases/case/details/$_POST:case_id',
            ],
        ],
        CSRFEnd::class => ['/'],
    ]
);
```
In this example when a support case is closed the following privately cached endpoints are deleted:
* **/user** - because it shows the number of support cases opened and closed.
* **/support-cases** - because it shows a list of support cases with opened cases placed first.
* **/support-cases/case/details/$_POST:case_id** - because it shows the details of the case being closed;
  **$_POST:case_id** it is replaced with the real value of `$_POST['case_id']` which was used to close the case.

You can use this logic with both **$_GET** and **$_POST**.
