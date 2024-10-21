# Middleware - Throttle::class

**Designation:** sets up rate limits for endpoint.<br>
**Throttle::class** can be used to throttle access to both **GET** and **POST** endpoints.


**Throttle::class** settings [array]:
* first element [number] (optional) - throttle hits weight. Number in range 0 < x < 1.

File **.env.ini** has the following throttle settings:
* **cache_enable** - Enables cache including throttle cache.
* **throttle_db.driver** - The driver used by throttle cache.
* **throttle_db.????** - Settings required by throttle_db.driver (see **.env.ini**).
* **throttle.hits** - The maximum number of hits.
* **throttle.interval** - Throttle interval (seconds).


```php
// File POSTRoutes.php.
$routes->add(
    '/change-user-details/request',
    ChangeUserDetailsController::class,
    'remote_request',
    [
        Throttle::class,
        // ...
    ]
);
```

