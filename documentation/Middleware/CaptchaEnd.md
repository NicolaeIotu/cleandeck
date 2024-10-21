# Middleware - CaptchaEnd::class

**Designation:** validate captcha data.<br>
**CaptchaEnd::class** can be used by both **GET** and **POST** endpoints.

**CaptchaEnd::class** settings [array]:
* first element [string] - Endpoint to redirect to in case of error.

## Add captcha data to a page
```php
// File Framework/Views/core/main/page-content/login.php.
// ...
<form method="post" action="<?php echo UrlUtils::baseUrl('/login/request'); ?>">
// ...
<?php echo view_main('components/captcha'); ?>
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
        CaptchaEnd::class => ['/login'],
    ]
);
```
In this example captcha information if validated and in case of errors the request is redirected to endpoint **GET /login**.
