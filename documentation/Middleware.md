# Middleware

Directory '**Framework/Middleware**' hosts Framework Middleware: Main and Addons.<br>
Directory '**Application/Instance/Middleware**' hosts custom Middleware.

## Framework Middleware

Middleware for each route is declared in route files
i.e. **Application/Routes/GETRoutes.php** and **Application/Routes/POSTRoutes.php**.<br>
Use parameter *$middleware* of method **$routes->add(...)** in order to declare the Middleware for each route.

```php
$routes->add(
    '/',
    HomeController::class,
    'index',
    // $middleware
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class,
        HttpCaching::class => ['interval' => 3600],
        CSP::class,
        SEO::class,
    ],
    [
        'changefreq' => 'daily',
        'priority' => 0.9,
    ]
);
```

Irrespective of the order set in route files, all framework middleware classes will run in a certain order.<br>
Class `Framework\Framework\Libraries\Routes\MiddlewareHandler` will enforce this order.

<hr>

### Framework Middleware - Main

1. [AAAInit::class](./Middleware/AAAInit.md "Middleware - AAAInit::class")
2. [Admin::class](./Middleware/Admin.md "Middleware - Admin::class")
3. [ApplicationStatusJWT::class](./Middleware/ApplicationStatusJWT.md "Middleware - ApplicationStatusJWT::class")
4. [CaptchaEnd::class](./Middleware/CaptchaEnd.md "Middleware - CaptchaEnd::class")
5. [CSP::class](./Middleware/CSP.md "Middleware - CSP::class")
6. [CSRFEnd::class](./Middleware/CSRFEnd.md "Middleware - CSRFEnd::class")
7. [HttpCaching::class](./Middleware/HttpCaching.md "Middleware - HttpCaching::class")
8. [SEO::class](./Middleware/SEO.md "Middleware - SEO::class")
9. [Throttle::class](./Middleware/Throttle.md "Middleware - Throttle::class")
10. [UserDetails::class](./Middleware/UserDetails.md "Middleware - UserDetails::class")

### Framework Middleware - Addon

Nothing at the moment.

### Custom Middleware

All Middleware must implement ```Framework\Framework\Middleware\MiddlewareInterface```.

Framework middleware runs before any custom/user-defined middleware on both **before** and **after** stages.
