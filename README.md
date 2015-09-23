# iProspect Canada PHP Codebase

A collection of classes tailored in-house that aim to help solve recurring concepts across our projects.


## How to include the codebase

Make sure our private Bitbucket repository is accessible in the project's `composer.json` file:

~~~ json
   "repositories": [
        {
            "type": "git",
            "url": "https://bitbucket.org/iprospect_ca/ip-codebase.git"
        }
    ]
~~~

Then you can add it as a requirement:

~~~ json
    "require": {
        "iprospect_ca/ip-codebase": ">=0.1.0",
    }
~~~

## Common & Strata PHP classes

The namespaces are autoloaded and project classes are expected to either use the objects as traits or to inherit from them.

## Wordpress functions

Because global functions are not namespaced, you have to manually include the file by hand in `function.php` or it's equivalent.

~~~ php
    include(dirname(dirname(ABSPATH)) . "/vendor/iprospect_ca/ip-codebase/src/Wordpress/tools.php");
~~~

Afterwards, the functions will be accessible across the project. Be wary of function name collisions.
