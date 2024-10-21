# Contributing

This project is designed to pair with a valid CMD-Auth installation and consume its Http REST Api.

For the moment the main goal of the application is to facilitate rapid development and deployment of applications which
are using CMD-Auth.

Part of the strategy used for CMD-Auth / CleanDeck stack is to recognize solid components along the path and
perform "low level flights" on top of these components. This means that the code should recognize the importance and
commit to a particular component. Ideally, for PHP this means that you should use clean PHP with no dependencies.

Contributions which require additional dependencies in composer.json have very slim chances to pass through. This means
that you have to use your PHP skills, and at the same time I/future team will have the chance to observe
the future members of the team.<br>
Some libraries which are only partly useful or cannot be trusted fully (this includes dependencies) may be included as
[Non-Standard Inclusions](./Non-Standard-Inclusions.md "Non-Standard Inclusions").

As one can easily notice there are missing features. If these missing features affect your operations don't hesitate
to contribute.

## Development

This is a rather complex framework and stack. All kinds of contributions are welcomed.
When compared with other PHP MVC frameworks, CleanDeck might seem a bit more difficult here and there,
so it may be easier to make contributions for advanced developers and developers familiar with the framework.

Additional skills may help: Linux/Unix, HTTP, REST, networking, PHP, Composer, Cloud APIs,
Cloud SDKs, APIs, frontend coding a.o.<br>
CMD-Auth (including the free version CMD-Auth Community Edition) is available as Linux package
so the development must be done on a Linux machine or connected to a Linux machine.

CMD-Auth - CleanDeck stack is designed, developed and produced on/for Linux,
so it is expected that the developers are well familiar with Linux.
Since this is a production grade stack, there is no intention to create versions or optimizations for other operating
systems.<br>
While it should work, the stack is not tested with containers.

## Testing

Tests are required for most parts of the code.

> composer test-dev

## Code Style and Code Analysis

Make sure your code matches the coding style of the application.<br>
Your code must also pass the analysis.

> composer cs-dev
>
> composer analyze-dev
>
> composer js-eslint

See **composer.json** for more scripts.

Make sure to inspect setup files in directory **tools** and comply with any requirements.

# CSS and Javascript libraries

A templating system is included with the framework. At the moment we will only handle the **core** template which is
based on Bootstrap.<br>
It is a good idea to take charge of other templates using probably different CSS and Javascript libraries.<br>
Custom templates must be mobile friendly.

# High Quality Output

The target is a clean 100% for Lighthouse reports. Some exceptions can be made for sections 'Accessibility' and
'SEO' only.

Before using and passing tests of other tools make sure to satisfy Lighthouse requirements first unless justified.

The header 'Content-Security-Policy' set with middleware 'CSP.php' should not be changed, and instead your code
should follow the policy set. The policy is debatable.

## Others

Keep the amount of features to the minimum necessary in such a way that other
programmers can continue the development of own applications. This is very important.
