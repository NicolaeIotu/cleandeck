# Middleware - SEO::class

**Designation:** Used to declare SEO endpoints.<br>
Used by GET endpoints.

There are no settings for this middleware.

As a reminder, SEO and [Sitemap](../Sitemap.md "Sitemap") features are decoupled.


```php
// File GETRoutes.php.
$routes->add(
    '/signup',
    SignupController::class,
    'index',
    [
        // ...
        SEO::class,
    ],
    [
        'changefreq' => 'weekly',
        'priority' => 0.8,
    ]
);
```

Once an endpoint is declared as SEO endpoint, pages can use this information for SEO related
purposes:

```php
// File Framework/Views/core/main/components/header.php.
// ...
$is_seo_page = CleanDeckStatics::isSeoPage();
// ...
    <?php if ($is_seo_page): ?>
<?php /* Do not modify below '<meta name="keywords" ... ' */ ?>
<meta name="keywords" content="##SEO_KEYWORDS##">
    <?php endif; ?>
<?php /* SEO description if any, must be provided by Controller. */ ?>
<meta name="description" content="<?= $seo_description ?? $page_title; ?>">
    <meta name="robots" content="<?php echo $is_seo_page ? 'index,follow' : 'noindex,nofollow'; ?>"/>
// ...
```

