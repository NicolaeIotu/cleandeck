# Routes

Routes are defined in route files which are regular .php files.

There are two main types of routes:
* Framework routes
* User routes

**Framework routes** are located in directories **Framework/Routes/Main** and
**Framework/Routes/Addon**.<br>
**User routes** are located in directory **Application/Instance/Routes**.

At the moment Framework routes files are named **GETRoutes.php** (for GET routes) and **POSTRoutes.php** (for POST routes).

Default User routes files names are **GETRoutes.php**, **GETRoutes_1.php**, **POSTRoutes.php** and **POSTRoutes_1.php**,
but these names can be changed by adjusting User routes configuration file
**Application/Instance/Config/Routes/UserRoutes.php**.

**IMPORTANT!** User routes are always loaded before Framework routes.

Splitting routes in 2 files based on request method is an innovative feature which separates CleanDeck from other
MVC frameworks:
* clear separation of routes based on request method
* faster lookup of routes:
  * the router will only load GET or POST route files
  * when a route is found, the rest of the entries in the route file are skipped. Place frequently used and/or important
    routes definitions to the beginning of the route file in order to get the best performance.

## Route definition

Routes are defined using method **$route->add(...)**.

```php
// Below the default (Framework) entry point of your application.
// File Framework/Routes/Main/GETRoutes.php.
$routes->add(
    '/', // the path
    HomeController::class, // the Controller
    'index', // Controller public function
    // Middleware (see Middleware.md)
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class,
        HttpCaching::class => ['interval' => 3600],
        CSP::class,
        SEO::class,
    ],
    // sitemap settings if any (see Sitemap.md)
    [
        'changefreq' => 'daily',
        'priority' => 0.9,
    ]
);
```

## Overwriting Framework routes

User routes are always loaded before Framework routes. This means that the User can simply declare own routes
having the same URIs as Framework routes and these User routes will always show instead of Framework routes.

In the example below the endpoint **GET /login** is redeclared and uses a custom login controller
`Application\Instance\Controllers\CustomLoginController`.

```php
// file Application/Instance/Routes/GETRoutes.php
$routes->add(
    '/login',
    Application\Instance\Controllers\CustomLoginController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class => [false, '/user'],
        HttpCaching::class => ['interval' => 86400],
        CSP::class,
        SEO::class,
    ],
    [
        'changefreq' => 'weekly',
        'priority' => 0.8,
    ]
);
```


## Eliminating Framework routes

Framework routes can be 'eliminated' by adding an artificial 404 response.<br>
In the example below endpoint **GET /confirm-password** is redeclared and acts just like a regular not found endpoint.

```php
// file Application/Instance/Routes/GETRoutes.php
$routes->add(
    '/confirm-password',
    ErrorController::class,
    'error404',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class,
        CSP::class,
    ]
);
```

## Display Application Routing

You can always check the routing for your application: ```composer exec cleandeck-routes```

## Relevant Resources

* [Middleware](./Middleware.md "Middleware")
* [Sitemap](./Sitemap.md "Sitemap")
