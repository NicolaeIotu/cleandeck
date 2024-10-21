# Middleware - ApplicationStatusJWT::class

**Designation:** handle JWT application status cookie of CMD-Auth.<br>
This cookie is used to signal various states of the application such as in-progress MFA authentication.


**ApplicationStatusJWT::class** must be used by all **GET** and **POST** endpoints which may be affected by
MFA authentication status. Most endpoints are affected by MFA authentication status.

**ApplicationStatusJWT::class** detects in-progress MFA authentication and redirects to MFA step 2 endpoint if required.
At the same time some endpoints can be accessed even if MFA authentication is in progress i.e. **/login-mfa-step-2**,
**/login-mfa-step-2/request** and **/login-mfa-cancel/request**.

Carefully judge the usage of this middleware. Basically most endpoints require this middleware with some exceptions.<br>
Some endpoints which must omit **ApplicationStatusJWT::class**:
* **GET /captcha**
* **GET /csrf**
* **GET /rss.xml**
* **GET /sitemap.xml**
* **CLI /cli/clear-cache**

There are no settings for this middleware.


```php
// File GETRoutes.php.
$routes->add(
    '/signup',
    SignupController::class,
    'index',
    [
        // ...
        ApplicationStatusJWT::class,
        // ...
    ],
    [
        'changefreq' => 'weekly',
        'priority' => 0.8,
    ]
);
```
