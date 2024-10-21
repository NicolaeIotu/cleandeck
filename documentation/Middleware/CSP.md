# Middleware - CSP::class

**Designation:** Enforces Content Security Protection for the application.<br>
Normally used for GET endpoints which output HTML content.

There are no settings for this middleware.


```php
// File GETRoutes.php.
$routes->add(
    '/user',
    UserController::class,
    'index',
    [
        // ...
        CSP::class,
    ]
);
```
