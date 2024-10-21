# Directory '**Application**'

Use Directory '**Application**' for own stuff:
* **Instance** - (M)VC content.
* **public** - HTTP directory (webserver document root directory).
* **writable** - Dynamically generated/updated content.


Directory **Instance** hosts your own application code:
* configuration including routing a.o.
* controllers
* middleware
* libraries
* views

Directory **public** hosts the entry point of the application **index.php**,
*robots.txt* file, *favicon.ico* file.<br>
Use directory **public/template/..** for publicly accessible static content i.e. css, js and images.
Use directory **public/misc/..** for publicly accessible dynamically generated content.
