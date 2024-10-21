# Development

CleanDeck offers very fast development/deployment and a pleasant flow.

With CleanDeck/CMD-Auth the data is totally under owner's control.

CleanDeck is not using Models so a developer will mainly handle Controllers and Views.
CleanDeck is not using helpers.

Start by reusing existing controllers and views. Adapting these should cover most use cases.

The following features are a bit more difficult and is probably not a good idea to start with:
* articles and FAQs
* uploads
* emails and files queues (local and cloud)
* AWS&reg; and Google&reg; interactions.

When developing own applications use only the directory **Application**.

The following default scripts act on directory **Application** and will make the development a lot easier for you:
* ```composer cs```
* ```composer cs-fix```
* ```composer analyze```
* ```composer rector-fix```
* ```composer test```
* ```composer js-eslint```

Other *composer* scripts target directory **Framework** which is normally out of the scope when developing
your own application.

Relevant resources for development:
* [DevOps](./DevOps.md "DevOps")
* [Middleware](./Middleware.md "Middleware")
* [Routes](./Routes.md "Routes")
* [SEO](./SEO.md "SEO")
* [Sitemap](./Sitemap.md "Sitemap")
* [Views](./Views.md "Views")
