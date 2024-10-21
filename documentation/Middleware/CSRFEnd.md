# Middleware - CSRFEnd::class

**Designation:** validate CSRF data.<br>
**CSRFEnd::class** can be used by both **GET** and **POST** endpoints.

**CSRFEnd::class** settings [array]:
* first element [string] - Endpoint to redirect to in case of error.

## Add CSRF data to a page
```php
// File Framework/Views/core/main/page-content/login.php.
// ...
<form method="post" enctype="application/x-www-form-urlencoded" action="<?php echo UrlUtils::baseUrl('/login/request'); ?>">
<?php echo view_main('components/csrf'); ?>
// ...
```

## Set up routing

```php
// File POSTRoutes.php.
$routes->add(
    '/login/request',
    LoginController::class,
    'remote_request',
    [
        // ...
        CSRFEnd::class => ['/login'],
    ]
);
```
In this example CSRF information if validated and in case of errors the request is redirected to endpoint **GET /login**.
