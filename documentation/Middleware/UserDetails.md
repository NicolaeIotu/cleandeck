# Middleware - UserDetails::class

**Designation:** handle authentication requirements; retrieve user details.<br>
**UserDetails::class** can be used by both **GET** and **POST** endpoints. This class determines authentication status
and acts as required by endpoint settings below. At the same time the user details are retrieved and stored as
PHP statics which can be used by other scripts.


**UserDetails::class** settings [array]:
* first element [boolean] - When setting this value to **true**, the user *must be authenticated* in order to access the endpoint.
 When setting this value to **false** the user *must not be authenticated* in order to access the endpoint.
* second element [string] - Endpoint to redirect to in case the first requirement is not met.


```php
// File GETRoutes.php.
$routes->add(
    '/activate-user',
    ActivateUserController::class,
    'remote_request',
    [
        // ...
        UserDetails::class => [false, '/'],
        // ...
    ]
);
```
In the example above the user cannot be authenticated in order to be granted access to the endpoint.
An authenticated user will be redirected to the home page.

```php
// File GETRoutes.php.
$routes->add(
    '/user',
    UserController::class,
    'index',
    [
        // ...
        UserDetails::class => [true, '/login'],
        // ...
    ]
);
```
In the example above the user must be authenticated in order to be granted access the endpoint.
A user which is not authenticated will be redirected to the login page.
