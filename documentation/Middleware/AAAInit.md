# Middleware - AAAInit::class

Under normal circumstances **AAAInit** is mandatory middleware and must be included in the list of middleware for each route.

**AAAInit** performs critical actions and checks.

```php
// File GETRoutes.php.
$routes->add(
    '/',
    HomeController::class,
    'index',
    [
        AAAInit::class,
        // ...
    ],
    [
        'changefreq' => 'daily',
        'priority' => 0.9,
    ]
);
```
