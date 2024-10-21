# SEO Optimizations

SEO optimizations and the Sitemap are decoupled. An endpoint can be SEO optimized without being added to the Sitemap.

SEO optimizations can be enabled by adding
**SEO::class** to the list of middleware for each GET endpoint.

If **SEO::class** is part of the middleware for an GET endpoint:

1. meta tag for *keywords* is automatically generated based on page output
2. **SEO Keywords** section shows up if setting ENVIRONMENT (root section \[cleandeck\]) is set to
   'development' (```ENVIRONMENT = development```) in file *.env.ini*. This section shows SEO keywords for the active
   page. Change the contents of the page in order to reach target SEO keywords.

For example:

```php
// file Application/Instance/Routes/GETRoutes.php
$routes->add(
    '/',
    HomeController::class,
    'index',
    [
        AAAInit::class,
        ApplicationStatusJWT::class,
        UserDetails::class,
        HttpCaching::class => ['interval' => 3600],
        CSP::class,
        SEO::class, // enables SEO Optimizations
    ],
    [
        'changefreq' => 'daily',
        'priority' => 0.9,
    ]
);
```

## Excluding content

Any content which is placed between comments ```<!--START-SEO-IGNORE-->``` and ```<!--END-SEO-IGNORE-->``` are not taken
into account when generating SEO Keywords.

## Meta Description

Tag **meta** for description is not handled by SEO Optimizations.

When using the **core** template, each Controller can set tag **meta** for *description*. In order to do so the
Controller must provide to the view (or individually to *header.php*) a data array which must have one of the keys
**seo_description** (primary) and/or **custom_page_name** (secondary).

```php
// file Framework/Controllers/Main/UserController.php
final class UserController
{
    public function index(): void
    {
        // ...
        $data = [
            // ...
            'seo_description' => 'Account Home',
            'custom_page_name' => 'Account Home',
        ];

        echo new HtmlView('main/page-content/authenticated/user/account_home', true, $data);
    }
    // ...
}
```
