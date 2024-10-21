# Middleware

Directory '**Framework/Views**' hosts Framework views: main and addons.<br>
Directory '**Application/Instance/Views**' hosts custom/user-defined views.

These directories are further divided in subdirectories named after the templates rendered such as template **core**.

Template directories are further divided in two subdirectories:
* **components** - hosts components which are often used by many pages i.e. **header.php** and **footer.php**
* **page-content** - hosts the main content of each page

Each template can set up its own logic. See below.

## Basic Views

There are 4 functions which are globally available and can be used in any controller in order to render views:
* **view** - requires an array of absolute paths pointing to view files
* **view_user** - requires an array of paths relative to ```Application/Instance/Views/env('cleandeck.template')```
* **view_main** - requires an array of paths relative to ```Framework/Views/env('cleandeck.template')/main```
* **view_addon** - requires an array of paths relative to ```Framework/Views/env('cleandeck.template')/addon```

All these functions render view files in the order provided. Data can be provided as the second argument to all these
functions.

```php
// Application/Instance/Controllers/MyCustomController.php
class MyCustomController
{
    public function index(): void
    {
        // ...

        $data = [
            'username' => 'Username',
            'details' => 'Details',
        ];

        echo \view_user('components/header', $data);
        echo \view_user('page-content/custom-page', $data);
        echo \view_user('components/footer', $data);
    }

    // ...
}
```

## Complex Views

Class ```Framework/Libraries/View/HtmlView``` is used by Framework controllers in order to display most
pages which can be rendered.
Class ```HtmlView``` uses settings *cleandeck.template*, *cleandeck.html_view_structure.header* and
*cleandeck.html_view_structure.footer* from file ```.env.ini```.

The user can use library ```HtmlView``` as an example when developing own complex ways of rendering views.
