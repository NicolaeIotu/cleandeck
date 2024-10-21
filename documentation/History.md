# History

## 1. CleanDeck / CMD-Auth stack

The stack is created and owned by me, Iotu Nicolae (nicolae.g.iotu@link133.com).

The development started sometime before 2020 when it was clear for me that there are no secure and cost-effective
mega-scale frameworks.

CleanDeck / CMD-Auth stack promotes security, performance, trust, quality and cost-efficiency.

## 2. The Path

The path was not clear from the beginning. There were many abandoned versions of both CleanDeck and CMD-Auth due to
common problems which still persist with most other active frameworks / stacks:
* over-reliance on third-party dependencies which equals insecurity
* unnecessary extra virtual layers which means poor performance translating to increased costs
* lack of quality revealed by running quality tools
* poor cost efficiency: the spending starts from the beginning of development and accelerates in production


### 2.1 The path to CMD-Auth

During development and online tests, CMD-Auth was using the name "Captain-Auth", a combo between
"Captain-America" and "Auth".

Finally, I changed the name to CMD-Auth which is shorter, technical and not so aggressive.

The initial version of CMD-Auth was created using NodeJS.
NodeJS has evolved tremendously and has all the tools required for complex programming tasks. I highly recommend
using NodeJS for backend where it fits perfectly. Modern versions of NodeJS include a lot of stuff out of the box.


### 2.2 The path to CleanDeck

It was not always clear that I need to build a framework in order to consume the output of CMD-Auth.

At the beginning I was using PHP framework CodeIgniter to create a CodeIgniter project adjusted to fit CMD-Auth
requirements. It all went well until because of the complexity I had to make changes to framework files and the
contributions proved to be difficult and time-consuming.

That's when I decided to create my own PHP MVC framework. The initial name was CleanDeckCombo. This framework
could handle all major PHP MVC frameworks and deployed on target the files required in order to consume
and display the content from CMD-Auth.

The problem with CleanDeckCombo was that PHP MVC frameworks need to be supervised and own content need to be adjusted
probably after each release of target PHP MVC frameworks. The amount of work required for such tasks is huge because
the authors of PHP MVC frameworks have own ideas used to monopolize the developers,
and they might just go on unexpected ways.

CleanDeckCombo may be an interesting concept, but it sure takes a lot of work maintaining it. I abandoned this
project and moved to CleanDeck, own framework designed specifically for CMD-Auth.

The name CleanDeck stands for a deck which is clean.<br>
In my opinion some frameworks are at least dangerous when fresh out of their shiny box.
CleanDeck is different in many ways. The PHP code must be written for CleanDeck specifically, composer.json must be clean
with absolute minimal dependencies which are reviewed periodically. When there is doubt regarding a library used then
parts of interest will be extracted, adjusted and included as non-standard dependency. That's a CleanDeck.

CleanDeckCombo inspired parts of CleanDeck:
* deployment: command **cleandeck-deploy** is inspired by CleanDeckCombo deployment
* a single library: developers can work on their own CleanDeck project and contribute to the framework from the same place
* archiving tools: **cleandeck-zip** and **cleandeck-unzip** are both inspired by CleanDeckCombo archiving tools
* other internal libraries

CleanDeck includes many innovative features. At the time of release no other PHP MVC frameworks are
using these innovative features:
* **.env.ini** - a **.ini** environment settings file. PHP is able to process dot ini files for a long time now.
 The processing is more efficient than any custom processing of **.env** files. As a result CleanDeck will be faster
 when compared with other PHP MVC frameworks.
* separate files for GET routes and POST routes - an obvious improvement: instead of processing a long list,
 process a maximum of approximately half of this list each time.
* deployment - a single command is sufficient in order to deploy and start using the application.
 See [cleandeck-deploy documentation](./Tools.md#cleandeck-deploy "cleandeck-deploy documentation").
* archiving - archiving tools [cleandeck-zip](./Tools.md#cleandeck-zip "cleandeck-zip documentation")
 and [cleandeck-unzip](./Tools.md#cleandeck-unzip "cleandeck-unzip documentation")
 will help with the transfer of your project to production server or other work location.

## 3. Errors and Bugs

A seemingly endless number of errors and bugs were solved during development. At the release date the behaviour
of the stack is stable.<br>
Please report errors and bugs. Contributors are rewarded.
See [CMD-Auth and CleanDeck Contribution Rewards](https://link133.com/article?title=CMD-Auth+and+CleanDeck+Contribution+Rewards  "CMD-Auth and CleanDeck Contribution Rewards").
