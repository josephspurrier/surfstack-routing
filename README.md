SurfStack Routing in PHP [![Build Status](https://travis-ci.org/josephspurrier/surfstack-routing.png)](https://travis-ci.org/josephspurrier/surfstack-routing)
========================

The SurfStack Router is a highly configurable PHP routing system. It was
designed to provide control and stability for RESTful web applications as
well as for those that need a solid foundation for their website.

It's 100% ready to go for those that don't need any customization and wide open
for those that want to hook into the pre/post mapping, pre/post dispatching,
errors, and even the dispatching piece itself. Once of the differences between
frameworks is the way methods and functions output data to the screen. Some
prefer you use a return statement while others use echo and print statements;
neither of these are an issue for the SurfStack Router. You have control over
the dispatch process so you can set up your own output buffering or handle the
content from a return statement and then output using one of the hooks.

The built-in dispatcher is able to call user functions, closures, static class
methods, and non static class methods from both the global space and
namespaces. All the routes are setup to look like a typical HTTP request so its
easy to actually test your routes using a REST client.

The SurfStack Router features preloaded and configurable wildcards so your
URL patterns no longer need to include regular expressions. You can preload
them individually or via array. Most of the configurations can be added one at
time or through arrays to save time. There is also no limit on the wildcards so
you can use multiples in a route. The router also supports dynamic class
methods so you can quickly turn a single class into a entire collection of
available routes.

There is a full set of unit tests for the routing pieces using PHPUnit. Take a
look at the tests if you would like to see route wildcard combinations.

The router also supports the X-HTTP-METHOD-OVERRIDE header for the webservers
that don't allow HTTP operations like PUT and DELETE.

## Tutorial Requirements
For this tutorial, we'll we have a class called: SurfStack\Test\TestClass.
The class will have two methods called: foo and bar.
There will also be a function at the bottom.

```php
<?php
namespace SurfStack\Test;

class TestClass
{
    function foo()
    {
        echo 'Hello world!';
    }
    
    function bar()
    {
        echo 'Hello universe!';
    }
}

function testFunction()
{
    echo 'Hello galaxy!';
}

```

## Creating an Instance of the Router Class

```php

// Create an instance of the Router
$router = new SurfStack\Routing\Router();

```

## Construction of a Route

You can set a route two different ways.

Syntax: setRoute(string $pattern, mixed $route [, array $arrOverrideParameters])
Syntax: setRoutes(array $arrRoutes)

```php

// Single route
$router->setRoute('GET /foo', array('SurfStack\Test\TestClass', 'foo'));

// Collection of routes
$router->setRoutes(array(
    'GET /foo'      => array('SurfStack\Test\TestClass', 'foo'),
    'GET /foobar'  => 'testFunction',
));

```

## Wildcards

The wildcards serve as basic input validation. If you want a certain type
of value to be passed to the called route, you can use a wildcard to designate
the value as a parameter.

These wilcards are already built in: {alpha}, {int}, *, **, {action}.
The {alpha} only allows letters and {int} only allows numbers. The * allows
letters, numbers, underscore, hyphen, plus, period, and percent. The ** adds
the allowance of slashes (only for those who want to chop up the
URL themselves). The {action} only works if the route is a class and allows any
method name in the class to be used.

Special considerations: Since the SurfStack router allows for multiple regex,
there are additional restrictions. The {action} and ** can only exist once in
the pattern. All the other wildcards can exist without restrictions. The 
{action} MUST be the first wildcard if it is used. If ** is not used last, be
very careful because the parameters will overlap. See the tests for more
information.

```php
<?php

// Dynamic Route accepts many URL
$router->setRoute('GET /foo/{int}', array('SurfStack\Test\TestClass', 'foo'));

// Dynamic Action Route selects the method based off the parameter
$router->setRoute('GET /foo/{action}', array('SurfStack\Test\TestClass'));

// Route uses a closure, passes wildcard value as parameter
$router->setRoute('GET /bar/*', function ($r, $param) {
    echo $param;
}; 

```

You can add your own wildcards and use them in your routes.

```php
<?php

// Set a single wildcard and regex
$a->setWildCardDefinition('{decimal}', '([0-9.]+)');

// Set multiple wildcards and regex
$a->setWildCardDefinitions(array(
    '{decimal}' => '([0-9.]+)',
    '{lowalpha}' => '([a-z]+)',
));

```
## Dispatching

There are two way to dispatch. You can go the automatic route which provides
you with a collection of hooks to tailor the behavior to your needs or the
manual route where you control it all yourself. 

```php
<?php

// *** Automatic Dispatching ***

// Does all the work for you
$router->dispatch('/foo/abc123/bar?def');

// *** OR ***
// *** Manual Dispatching ***

// Pass the user requested URL
$router->map('/foo');

// If the URL matches a route (page found)
if ($router->isRouteMapped())
{
    call_user_func_array($router->getCallableRoute(), $router->getParameters());
}
else
{
    if ($router->getMapType() == $router::C_ROUTE_ERROR)
    {
        header('HTTP/1.0 500 Internal Server Error');
        echo 'Error: '.$router->getError();
    }
    else if ($router->getMapType() == $router::C_ROUTE_NOT_FOUND)
    {
        header("HTTP/1.0 404 Not Found");
        echo 'Not found';
    }
}

```

## Hooks

There are 8 hooks you can utilize if you choose the automatic dispatching. They
called in this order:
* $router::C_HOOK_BEFORE_MAP
* $router::C_HOOK_AFTER_MAP
* $router::C_HOOK_BEFORE_DISPATCH
* $router::C_HOOK_PARAMETER_LOGIC
*     $router::C_HOOK_DISPATCH - only if route is found
*     $router::C_HOOK_NOT_FOUND - only if route is not found
*     $router::C_HOOK_ERROR - only if a problem occurred with the configuration
* $router::C_HOOK_AFTER_DISPATCH

The hooks are easy to use and receive your $router object as a parameter 0.
The hooks can be any type of callable entity. C_HOOK_PARAMETER_LOGIC is used to
change the logic for the getCallableParameters() method.

```php
<?php

// Modfy main dispatch so you store the return value
$router->setHook($router::C_HOOK_DISPATCH, function (&$r) {
    $r->output = call_user_func_array($r->getCallableRoute(), $r->getCallableParameters());
});

// Output the return value at the end of the operation
$router->setHook($router::C_HOOK_AFTER_DISPATCH, function ($r) {
    echo $r->output;
});

```

## Secondary and Override Parameters

One of the nice features is the ability to set secondary and override
parameters. If you have a class that needs certains parameters passed to them,
you can assign the secondary parameters to the same exact route. These
secondary parameters will be merged with the parameters extracted from the
wildcards. The override parameters do just as they sound. They override the
primary and secondary parameters so you can control which parameters are
passed. When an override is specified, the router class will not be passed
as parameter 0. These are extremely beneficial when unit testing your classes.

Regardless, you can still access all three sets of parameters from the public
methods:
* $router->getParameters()
* $router->getSecondaryParameters()
* $router->getOverrideParameters()

```php
<?php

// Set the secondary parameters to a single route
$router->setOverrideParameter('PUT /foo/*', array('secondary1', 'secondary2'));

// Set the secondary parameters for multiple routes
$router->setOverrideParameters(array(
    'GET /foo/{int}'  => array('secondary1', 'secondary2'),
    'GET /bar/*'      => array('secondary3', 'secondary4'),

// Set the override parameters to a single route
$router->setOverrideParameter('PUT /foo/*', array(), array('override1', 'override2'));

// Set the override parameters for multiple routes
$router->setOverrideParameters(array(
    'GET /foo/{int}'  => array('override1', 'override2'),
    'GET /bar/*'      => array('override3', 'override4'),
));

```

## Tips

By using the C_HOOK_PARAMETER_LOGIC hook, you can customize the logic for how
the parameters are passed to the callable entity. If you always want the
secondary parameters to be passed to the callable entity first, you could set
your hook to something like this:

```php
<?php

// Modfy parameter logic
$router->setHook($router::C_HOOK_PARAMETER_LOGIC, function ($r) {
    // Use the override parameters, else use the standard parameters
    $params = ( $r->getOverrideParameters()
        ? $r->getOverrideParameters()
        : $r->getParameters());
    // Return the secondary parameters, then $router, then other parameters
    return array_merge($r->getSecondaryParameters(), array($r), $params);
});

```

To save some memory, call this when you are finished using the SurfStack
Router to prevent memory leaks from closures that use an instance of $router.

```php
<?php

// Free memory
$router->destroy();
unset($router);

```

You can access the parameters and query string using these methods:
```php
<?php

$router->getParameter(1);
$router->getQuery('paged');

```

You can get or test the HTTP request method using these methods:

* $router->getRequestMethod()
* $router->isCONNECT()
* $router->isDELETE()
* $router->isGET()
* $router->isHEAD()
* $router->isOPTIONS()
* $router->isPATCH()
* $router->isPOST()
* $router->isPUT()
* $router->isTRACE()

You can also extend the Router class and add any features that you need.

To install using composer, use the code from the Wiki page [Composer Wiki page](../../wiki/Composer).
