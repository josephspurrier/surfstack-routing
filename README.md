SurfStack Routing in PHP [![Build Status](https://travis-ci.org/josephspurrier/surfstack-routing.png)](https://travis-ci.org/josephspurrier/surfstack-routing)
========================

Single class that determines if a request URL maps to a class method, function, 
or anonymous function based on routes. There is also a built in dispatcher that
will call the correct structure if you choose to use it (not required). You can
extend the Router class to change any piece you would like, especially the
extensible dispatching methods:
* beforeMap
* afterMap
* beforeDispatch
* afterDispatch
* showNotFound
* showError

There is a full set of unit tests for the routing pieces using PHPUnit. Take a
look at the tests if you would like to see route wildcard combinations.

For this example, we'll assume you have a class called: SurfStack\Test\TestClass.
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

You would like to map each of these methods so they can be accessed from
different URLs.
In your main application file like index.php, follow these instructions.

```php
<?php

// Create an instance of the Router
$router = new SurfStack\Routing\Router();

// Create an associative array for the Routes.
// The key should be the URL.
//
// The key should always start with a slash and should not end with a slash.
// The key supports wildcards:
// {action} - alphanumeric including underscore
// {integer} - integers only
// {string} - alphabet only
// * - alphanumeric including underscore, hyphen, plus, period, percent
// ** - alphanumeric including underscore, hyphen, plus, period, percent, slash
//
// The value should be one of the following:
// * an array of a class name and a method name (in that order, has to be public)
// * a string of a function name
// * an anonymous function
//
// The wildcards {action} and ** can only exist ONCE in the key. All the other
// wildcards can exist as many times as you would like in the key.
// The wildcard {action} MUST be the first wildcard and can only be used with
// the Class Method route.
// If the wildcard ** is not used last, be very careful because the parameters
// will overlap. See the tests for more information.
// Static Routes only accept one URL.
// Dynamic Routes accept more than one URL because they use wildcards.
$routes = array(
    // This is a Static, Class Method Route
    // This route is only mapped when the user accesses http://example.com/foo
    '/foo' => array('SurfStack\Test\TestClass', 'foo'),
    
    // This is a Dynamic, Class Method Route
    // This route is mapped when the user accesses any of the following:
    // * http://example.com/foo/123/bar
    // * http://example.com/foo/abc/bar
    // * http://example.com/foo/abc123/bar
    '/foo/*/bar' => array('SurfStack\Test\TestClass', 'bar'),
    
    // This is a Static, Function Route
    '/func' => 'testFunction',
    
    // This is a Static, Anonymous Function Route
    '/anon' => function () { echo 'Anonymous function.'; },
    
    // This is a Dynamic, Anonymous Function Route
    '/anon/*' => function ($param1) { echo 'Anonymous function: '.$param1; },
    
    // This is a Dynamic, Class Method Route using {action} and a wildcard
    // This route excludes the method parameter so the user can specify
    // using the action parameter
    // This route is mapped when the user accesses any of the following:
    // * http://example.com/test/foo/123
    // * http://example.com/test/bar/abc
    '/test/{action}/*' => array('SurfStack\Test\TestClass'),
);

// Pass the array of routes
$router->setRoutes($routes);

// *** Manual Dispatching ***

// Pass the user requested URL
// This can be retrieved from $_SERVER['REQUEST_URI']
// Like this: $router->map($_SERVER['REQUEST_URI']); 
// Method will automatically remove the query string
$router->map('/foo/abc123/bar?def');

// If the URL matches a route (page found)
if ($router->isRouteMapped())
{
    if ($router->getMapType() == $router::C_CLASSMETHOD)
    {
        //print_r($router->getMappedClass());         // Outputs: SurfStack\Test\TestClass
        //print_r($router->getMappedMethod());        // Outputs: bar
        //print_r($router->getParameters());          // Outputs: Array ( [0] => abc123 )
        
        $objClass = $router->getMappedClass();
        
        // Call Class Method
        call_user_func_array(
            array(
                new $objClass(),
                $router->getMappedMethod(),
            ),
            $router->getParameters()
        );
    }
    else if ($router->getMapType() == $router::C_FUNCTION)
    {        
        // Call Function
        call_user_func_array(
            $router->getMappedFunction(),
            $router->getParameters()
        );
    }
    else if ($router->getMapType() == $router::C_ANONFUNCTION)
    {        
        // Call Anonymous Function
        call_user_func_array(
            $router->getMappedAnonFunction(),
            $router->getParameters()
        );
    }
}
// Else the URL does NOT match a route (error or page not found)
else
{
    if ($router->getMapType() == $router::C_ERROR)
    {
        $router->showError();                         // Outputs: (500 error message)
    }
    else if ($router->getMapType() == $router::C_NOTFOUND)
    {
        $router->showNotFound();                      // Outputs: (404 not found)
    }
}

// *** OR ***

// *** Automatic Dispatching ***

// This can be used as a dispatcher that determines if they actually exists;
// and then automatically calls the class method, function or anonymous
// function
// Pass the user requested URL
// This can be retrieved from $_SERVER['REQUEST_URI']
// Like this: $router->dispatch($_SERVER['REQUEST_URI']);
// Method will automatically remove the query string
$router->dispatch('/foo/abc123/bar?def');
```

To install using composer, use the code from the Wiki page [Composer Wiki page](../../wiki/Composer).
