SurfStack Routing in PHP
===============================

Single class that determines if a request URL maps to a class and method.

For this example, we'll assume you have a class called: SurfStack\Test\TestClass.
The class will have two methods called: foo and bar.

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
```

You would like to map each of these methods so they can be access from
different URLs.
In your main application file like index.php, follow these instructions.

```php
<?php

// Create an instance of the Router
$router = new SurfStack\Routing\Router();

// Create an associative array for the Routes.
// The key should be the URL. The value should be an array of a class name and a
// method name (in that order). The method name is optional so if it is left
// out, it will use the method name, 'index'. The method should also be public.
// The key should always start with a slash and should not end with a slash.
// The key supports wildcards:
// {action} - alphanumeric including underscore
// {integer} - integers only
// {string} - alphabet only
// * - alphanumeric including underscore, hyphen, plus, period, percent
// ** - alphanumeric including underscore, hyphen, plus, period, percent, slash
//
// The wildcards {action} and ** can only exist ONCE in the key. All the other
// wildcards can exist as many times as you would like.
// The wildcard {action} MUST be the first wildcard.
// If the wildcard ** is not used last, be very careful because the parameters
// will overlap.
// Static Routes only accept one URL.
// Dynamic Routes accept more than one URL because they use wildcards.
$routes = array(
    // This is a Static Route
    // This route is only mapped when the user accesses http://example.com/foo
    '/foo'          => array('SurfStack\Test\TestClass', 'foo'),
    // This is a Dynamic Route
    // This route is mapped when the user accesses any of the following:
    // * http://example.com/foo/123/bar
    // * http://example.com/foo/abc/bar
    // * http://example.com/foo/abc123/bar
    '/foo/*/bar'    => array('SurfStack\Test\TestClass', 'bar'),
);

// Pass the array of routes
$router->setRoutes($routes);

// Pass the user requested URL
// This should be retrieved from $_SERVER['REQUEST_URI'] 
// Method will automatically remove the query string
$router->map('/foo/abc123/bar?def');

// If the URL matches a route
if ($router->isRouteMapped())
{
    // Get the mapped class
    $className = $router->getMappedClass();
    
    // Get the mapped method
    $methodName = $router->getMappedMethod();
    
    // Get the parameters designated by wildcards
    $arrParams = $router->getParameters();
    
    echo 'Page found';
    
    // Just for visualization
    print_r($router->getMappedClass());   // Outputs: SurfStack\Test\TestClass
    print_r($router->getMappedMethod());  // Outputs: bar
    print_r($router->getParameters());    // Outputs: Array ( [0] => abc123 )
}
// Else the URL does not match any routes
else
{
    echo 'Page not found';
}
```