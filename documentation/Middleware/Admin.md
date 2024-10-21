# Middleware - Admin::class

**Designation:** handle administration tasks.<br>
**Admin::class** can be used to restrict access to both **GET** and **POST** endpoints.
The restriction is implemented using the account rank.


**Admin::class** settings [array]:
* first element [integer] - the minimum account rank required in order to grant access to the endpoint


```php
// File GETRoutes.php.
$routes->add(
    '/admin/article/new',
    ArticleLifecycleController::class,
    'admin_article_new',
    [
        // ...
        Admin::class => [1000],
        // ...
    ]
);
```
In this example users having an account rank which is less than 1000 cannot initiate the procedure of creating
a new article.
