# Middleware

Directory '**Framework/Views**' hosts Framework views: main and addons.<br>
Directory '**Application/Instance/Views**' hosts custom user-defined views.

These directories are further divided in subdirectories named after the templates rendered such as template **core**.

Template directories are further divided in two subdirectories:
* **components** - hosts components which are often used by many pages i.e. **head.php** and **footer.php**
* **page-content** - hosts the main content of each page

Each template can set up its own logic. See below.

## Basic Views

There are 4 functions which are globally available and can be used in any controller in order to render views:
* **view** - requires an array of absolute paths pointing to view files
* **view_app** - requires a path relative to ```Application/Instance/Views/env('cleandeck.template')```
* **view_main** - requires a path relative to ```Framework/Views/env('cleandeck.template')/main```
* **view_addon** - requires a path relative to ```Framework/Views/env('cleandeck.template')/addon```

All these functions render view files in the order provided. Data can be provided as the second argument to all these
functions.

```php
// Application/Instance/Controllers/MyCustomController.php
class MyCustomController
{
    public function ajax_handler(): void
    {
        // ...

        $data = [
            'username' => 'Username',
            'details' => 'Details',
        ];

        echo \view_app('custom-page', $data);
    }

    // ...
}
```

## Complex Views

Class ```Framework/Libraries/View/HtmlView``` can construct complex views with components and page-content
which belong exclusively to one of the categories: _Framework_, _Application_ or _Addon_.
Class ```Framework/Libraries/View/HtmlView``` will use the setting *cleandeck.template* from file **.env.ini**.

In order to construct complex views with mixed components and page-content you have to create
your own HtmlView preferably in directory ```Application/Instance/Libraries/View```.
When creating a custom HtmlView, start by copying class ```Framework/Libraries/View/HtmlView```
and adjust it as required.
