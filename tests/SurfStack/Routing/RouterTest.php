<?php

/**
 * This file is part of the SurfStack package.
 *
 * @package SurfStack
 * @copyright Copyright (C) Joseph Spurrier. All rights reserved.
 * @author Joseph Spurrier (http://josephspurrier.com)
 * @license http://www.apache.org/licenses/LICENSE-2.0.html
 */

use SurfStack\AccessControl\AccessHandler;

/**
 * Router Test
 * 
 * Ensures the class maps routes as expected
 *
 */
class RouterTest extends PHPUnit_Framework_TestCase
{
    public function testNoMap()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
        
        // Pass no routes
        
        // Pass URL
        $a->map('/foo/abc123/bar?def');
        
        // Should not find route
        $this->assertFalse($a->isRouteMapped());
    }
    
    public function testMapStaticClassMethod()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
        
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo' => array($class, $method),
        ));
    
        // Pass URL
        $a->map('/foo?def');
    
        // Should find route
        $this->assertTrue($a->isRouteMapped());
        
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
        
        // Get the parameters
        $this->assertSame($a->getParameters(), array());
    }
    
    public function testMapStaticFunction()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $function = 'testFunction';
    
        function testFunction()
        {
            echo 'Hello';
        }
        
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo' => $function,
        ));
    
        // Pass URL
        $a->map('/foo?def');
        
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
        
        // Get the parameters
        $this->assertSame($a->getParameters(), array());
    }
    
    public function testMapStaticClosure()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $closure = function () {
            echo 'Hello';
        };
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo' => $closure,
        ));
    
        // Pass URL
        $a->map('/foo?def');
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array());
    }
    
    public function testMapDynamicClosureWildcardWildcard()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $closure = function ($param1, $param2)
        {
            echo "$param1 $param2";
        };
        
        $pm1 = 'abc';
        $pm2 = '123'; 
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/*/*' => $closure,
        ));
    
        // Pass URL
        $a->map("/foo/$pm1/$pm2?def");
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
        
        // Get the parameters
        $this->assertSame($a->getParameters(), array($pm1, $pm2));
    }
    
    public function testMapDynamicClosureWildcardDoubleWildcard()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $closure = function ($param1, $param2) {
            echo "$param1 $param2";
        };
    
        $pm1 = 'abc';
        $pm2 = '123/def/ghi';
    
        // Pass the array of routes
        $a->setRoutes(array(
        'GET /foo/*/**' => $closure,
        ));
    
        // Pass URL
        $a->map("/foo/$pm1/$pm2?def");
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array($pm1, $pm2));
    }
    
    /**
     * @expectedException BadMethodCallException
     */
    public function testMapNoMethodStatic()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo' => array($class),    // Method is missing
        ));
    
        // Pass URL
        $a->map('/foo?def');
    }
    
    /**
     * @expectedException BadMethodCallException
     */
    public function testMapNoMethodDynamic()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/{int}' => array($class),    // Method is missing
        ));
    
        // Pass URL
        $a->map('/foo/27?def');
    }
    
    /**
     * @expectedException BadFunctionCallException
     */
    public function testMapMissingFunction()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo' => 'functionDontExist',    // Method is missing
        ));
    
        // Pass URL
        $a->map('/foo?def');
    }
    
    public function testMapDynamicAction()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $action = 'bar';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/{action}' => array($class),
        ));
    
        // Pass URL
        $a->map("/foo/$action?def");
    
        // Should find route
        $this->assertTrue($a->isRouteMapped());
        
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array());
    }
    
    /**
     * @expectedException BadMethodCallException
     */
    public function testMapStaticNotEnough()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo' => array(),
        ));
    
        // Pass URL
        $a->map("/foo?def");
    }
    
    /**
     * @expectedException BadMethodCallException
     */
    public function testMapDynamicActionNotEnough()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $action = 'bar';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/*' => array(),
        ));
    
        // Pass URL
        $a->map("/foo/test?def");
    }
    
    /**
     * @expectedException BadMethodCallException
     */
    public function testMapDynamicActionTooMany()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $action = 'bar';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/{action}' => array($class, 'bar'),
        ));
    
        // Pass URL
        $a->map("/foo/$action?def");
    }
    
    /**
     * @expectedException BadMethodCallException
     */
    public function testMapDynamicTooMany()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $action = 'bar';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/' => array($class, 'bar', 'random'),
        ));
    
        // Pass URL
        $a->map("/foo/?def");
    }
    
    /**
     * @expectedException UnexpectedValueException
     */
    public function testMapDynamicBadObject()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $action = 'bar';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/' => new stdClass(),
        ));
    
        // Pass URL
        $a->map("/foo/?def");
    }
    
    public function testMapDynamicActionWildcard()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $action = 'bar';
        $wildcard = 'ghi123';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/{action}/test/*' => array($class),
        ));
    
        // Pass URL
        $a->map("/foo/$action/test/$wildcard?def");
    
        // Should find route
        $this->assertTrue($a->isRouteMapped());
        
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array($wildcard));
        
        // Get the parameter
        $this->assertSame($a->getParameter(1), $wildcard);
    }
    
    public function testMapDynamicActionDoubleWildcard()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $action = 'bar';
        $wildcard = 'ghi123/jkl456/lmnop';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/{action}/test/**' => array($class),
        ));
    
        // Pass URL
        $a->map("/foo/$action/test/$wildcard?def");
    
        // Should find route
        $this->assertTrue($a->isRouteMapped());
        
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array($wildcard));
    }
    
    public function testMapDynamicActionWildcardString()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $action = 'bar';
        $wildcard = 'ghi123';
        $wildstring = 'jkl';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/{action}/test/*/{alpha}' => array($class),
        ));
    
        // Pass URL
        $a->map("/foo/$action/test/$wildcard/$wildstring?def");
    
        // Should find route
        $this->assertTrue($a->isRouteMapped());
        
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array($wildcard, $wildstring));
    }
    
    /**
     * @expectedException BadMethodCallException
     */
    public function testMapDynamicWildcardActionStringProblem()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $action = 'bar';
        $wildcard = 'ghi123';
        $wildstring = 'jkl';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/*/test/{action}/{alpha}' => array($class),
        ));
    
        // Pass URL
        $a->map("/foo/$wildcard/test/$action/$wildstring?def");
    }
    
    public function testMapDynamicActionWildcardStringInteger()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $action = 'bar';
        $wildcard = 'ghi123';
        $wildstring = 'jkl';
        $wildint = '456';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/{action}/test/*/{alpha}/{int}' => array($class),
        ));
    
        // Pass URL
        $a->map("/foo/$action/test/$wildcard/$wildstring/$wildint?def");
    
        // Should find route
        $this->assertTrue($a->isRouteMapped());
        
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array($wildcard, $wildstring, $wildint));
    }
    
    public function testMapDynamicActionWildcardWildcard()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $action = 'bar';
        $wildcard1 = 'ghi123';
        $wildcard2 = 'jkl+-_%456';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/{action}/test/*/*' => array($class),
        ));
    
        // Pass URL
        $a->map("/foo/$action/test/$wildcard1/$wildcard2?def");
    
        // Should find route
        $this->assertTrue($a->isRouteMapped());
        
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array($wildcard1, $wildcard2));
    }
    
    public function testMapDynamicWildcardWildcard()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
        $wildcard1 = 'ghi123';
        $wildcard2 = 'jkl+-_%456';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/test/*/*' => array($class, $method),
        ));
    
        // Pass URL
        $a->map("/foo/test/$wildcard1/$wildcard2?def");
    
        // Should find route
        $this->assertTrue($a->isRouteMapped());
        
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array($wildcard1, $wildcard2));
    }
    
    public function testMapDynamicWildcardDoubleWildcard()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
        $wildcard1 = 'ghi123';
        $wildcard2 = 'jkl+-_%456/a/b/c/d';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/test/*/**' => array($class, $method),
        ));
    
        // Pass URL
        $a->map("/foo/test/$wildcard1/$wildcard2?def");
    
        // Should find route
        $this->assertTrue($a->isRouteMapped());
        
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array($wildcard1, $wildcard2));
    }
    
    public function testMapDynamicDoubleWildcardWildcard()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
        $wildcard1 = 'ghi123';
        $wildcard2 = 'jkl+-_%456/a/b/c/file.php';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/test/**/*' => array($class, $method),
        ));
    
        // Pass URL
        $a->map("/foo/test/$wildcard1/$wildcard2?def");
    
        // Should find route
        $this->assertTrue($a->isRouteMapped());
        
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array('ghi123/jkl+-_%456/a/b/c', 'file.php'));
    }

    public function testsetRoute()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
        
        $class = 'TestClass';
        $method = 'foo';
        
        // Pass single of route
        $a->setRoute('GET /foo', array($class, $method));
        
        // Pass URL
        $a->map('/foo?def');
        
        // Should find route
        $this->assertTrue($a->isRouteMapped());
        
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
        
        // Get the parameters
        $this->assertSame($a->getParameters(), array());
    }
    
    public function testHookNotFound()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
        
        $class = 'TestClass';
        $method = 'foo';
        
        // Pass single of route
        $a->setRoute('GET /foo', array($class, $method));
        
        // Value to change
        $test = false;
        
        // Add hook
        $a->setHook($a::C_HOOK_NOT_FOUND, function () use (&$test) {
            $test = true;
        });
        
        // Run the logic
        $a->dispatch('/foo2');
        
        // Should change value
        $this->assertTrue($test);
    }

    public function testHookMap()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
    
        // Pass single of route
        $a->setRoute('GET /foo', array($class, $method));
    
        // Value to change
        $test1 = false;
        $test2 = false;
    
        // Add hook
        $a->setHook($a::C_HOOK_BEFORE_MAP, function () use (&$test1) {
            $test1 = true;
        });
        $a->setHook($a::C_HOOK_AFTER_MAP, function () use (&$test2) {
            $test2 = true;
        });
        
        // Run the logic
        $a->dispatch('/foo');
        
        // Should change value
        $this->assertTrue($test1);
        $this->assertTrue($test2);
    }
    
    public function testHooksMap()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
    
        // Pass single of route
        $a->setRoute('GET /foo', array($class, $method));
    
        // Value to change
        $test1 = false;
        $test2 = false;
    
        // Add hook
        $a->setHooks(array(
            $a::C_HOOK_BEFORE_MAP => function () use (&$test1) { $test1 = true; },
            $a::C_HOOK_AFTER_MAP => function () use (&$test2) { $test2 = true; },
        ));
    
        // Run the logic
        $a->dispatch('/foo');

        // Should change value
        $this->assertTrue($test1);
        $this->assertTrue($test2);
    }
    
    /**
     * @expectedException BadFunctionCallException
     */
    public function testHookMapBad()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
    
        // Pass single of route
        $a->setRoute('GET /foo', array($class, $method));
    
        // Value to change
        $test1 = false;
    
        // Add hook
        $a->setHook($a::C_HOOK_BEFORE_MAP, 'causeAnError');

        // Run the logic
        $a->dispatch('/foo');
    }
    
    public function testHookDispatch()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
    
        // Pass single of route
        $a->setRoute('GET /foo', array($class, $method));
    
        // Value to change
        $test1 = false;
        $test2 = false;
    
        // Add hook
        $a->setHook($a::C_HOOK_BEFORE_DISPATCH, function () use (&$test1) {
            $test1 = true;
        });
        $a->setHook($a::C_HOOK_AFTER_DISPATCH, function () use (&$test2) {
            $test2 = true;
        });
        
        // Run the logic
        $a->dispatch('/foo');
        
        // Should change value
        $this->assertTrue($test1);
        $this->assertTrue($test2);
    }
    
    public function testHookOrder()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
    
        // Pass single of route
        $a->setRoute('GET /foo', array($class, $method));
    
        // Value to change
        $test = '';
    
        // Add hooks
        $a->setHook($a::C_HOOK_BEFORE_DISPATCH, function () use (&$test) {
            $test .= 'beforeDispatch,';
        });
        $a->setHook($a::C_HOOK_ROUTE_VALIDATION, function () use (&$test) {
            $test .= 'routeValidation,';
        });
        $a->setHook($a::C_HOOK_AFTER_DISPATCH, function () use (&$test) {
            $test .= 'afterDispatch,';
        });
        $a->setHook($a::C_HOOK_BEFORE_MAP, function () use (&$test) {
            $test .= 'beforeMAP,';
        });
        $a->setHook($a::C_HOOK_AFTER_MAP, function () use (&$test) {
            $test .= 'afterMAP,';
        });
        $a->setHook($a::C_HOOK_DISPATCH, function ($r) use (&$test) {
            $test .= 'hookDispatch,';
            $r->getCallableRoute();
            $r->getCallableParameters();
        });
        $a->setHook($a::C_HOOK_PARAMETER_LOGIC, function () use (&$test) {
            $test .= 'hookParameter,';
        });
        $a->setHook($a::C_HOOK_ROUTE_LOGIC, function () use (&$test) {
            $test .= 'hookRoute,';
        });
    
        // Run the logic
        $a->dispatch('/foo');

        // Should change value
        $this->assertSame($test, 'beforeMAP,routeValidation,afterMAP,beforeDispatch,hookDispatch,hookRoute,hookParameter,afterDispatch,');
    }
    
    public function testHookOrderSharing()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
    
        // Pass single of route
        $a->setRoute('GET /foo', array($class, $method));
    
        // Value to change
        $test = '';
    
        // Add hooks
        $a->setHook($a::C_HOOK_BEFORE_MAP, function ($r) {
            $r->test = 'beforeMAP,';
        });
        $a->setHook($a::C_HOOK_AFTER_MAP, function ($r) {
            $r->test .= 'afterMAP,';
        });
        $a->setHook($a::C_HOOK_BEFORE_DISPATCH, function ($r) {
            $r->test .= 'beforeDispatch,';
        });
        $a->setHook($a::C_HOOK_DISPATCH, function ($r) {
            $r->test .= 'hookDispatch,';
        });
        // Set test equal to the values shared between closures
        $a->setHook($a::C_HOOK_AFTER_DISPATCH, function ($r) use (&$test) {
            $test = $r->test.'afterDispatch,';
        });
    
        // Run the logic
        $a->dispatch('/foo');

        // Should change value
        $this->assertSame($test, 'beforeMAP,afterMAP,beforeDispatch,hookDispatch,afterDispatch,');
    }

    public function testPost()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
        
        $class = 'TestClass';
        $method = 'foo';
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Pass single of route
        $a->setRoute('POST /foo', array($class, $method));
        
        // Pass URL
        $a->map('/foo?def');
        
        // Should find route
        $this->assertTrue($a->isRouteMapped());
        
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
        
        // Get the parameters
        $this->assertSame($a->getParameters(), array());
        
        // Check trues
        $this->assertTrue($a->isPOST());
        $this->assertFalse($a->isGET());
        $this->assertFalse($a->isHEAD());
        $this->assertFalse($a->isPATCH());
        $this->assertFalse($a->isDELETE());
        $this->assertFalse($a->isPUT());
        $this->assertFalse($a->isOPTIONS());
        $this->assertFalse($a->isCONNECT());
        $this->assertFalse($a->isTRACE());
    }
    
    public function testAnyStatic()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
    
        $_SERVER['REQUEST_METHOD'] = 'POST';
    
        // Pass single of route
        $a->setRoute('ANY /foo', array($class, $method));
    
        // Pass URL
        $a->map('/foo?def');
    
        // Should find route
        $this->assertTrue($a->isRouteMapped());
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array());
    
        // Check trues
        $this->assertTrue($a->isPOST());
        $this->assertFalse($a->isGET());
        $this->assertFalse($a->isHEAD());
        $this->assertFalse($a->isPATCH());
        $this->assertFalse($a->isDELETE());
        $this->assertFalse($a->isPUT());
        $this->assertFalse($a->isOPTIONS());
        $this->assertFalse($a->isCONNECT());
        $this->assertFalse($a->isTRACE());
    }
    
    public function testAnyDynamic()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
    
        $_SERVER['REQUEST_METHOD'] = 'POST';
    
        // Pass single of route
        $a->setRoute('ANY /foo/*', array($class, $method));
    
        // Pass URL
        $a->map('/foo/abc?def');
    
        // Should find route
        $this->assertTrue($a->isRouteMapped());
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array('abc'));
    
        // Check trues
        $this->assertTrue($a->isPOST());
        $this->assertFalse($a->isGET());
        $this->assertFalse($a->isHEAD());
        $this->assertFalse($a->isPATCH());
        $this->assertFalse($a->isDELETE());
        $this->assertFalse($a->isPUT());
        $this->assertFalse($a->isOPTIONS());
        $this->assertFalse($a->isCONNECT());
        $this->assertFalse($a->isTRACE());
    }
    
    public function testPostNotGet()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
    
        $_SERVER['REQUEST_METHOD'] = 'GET';
    
        // Pass single of route
        $a->setRoute('POST /foo', array($class, $method));
    
        // Pass URL
        $a->map('/foo?def');
    
        // Should find NOT route
        $this->assertFalse($a->isRouteMapped());
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_NOT_FOUND);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array());
    
        // Check trues
        $this->assertFalse($a->isPOST());
        $this->assertTrue($a->isGET());
        $this->assertFalse($a->isHEAD());
        $this->assertFalse($a->isPATCH());
        $this->assertFalse($a->isDELETE());
        $this->assertFalse($a->isPUT());
        $this->assertFalse($a->isOPTIONS());
        $this->assertFalse($a->isCONNECT());
        $this->assertFalse($a->isTRACE());
    }

    public function testOverrideOutside()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $test = false;
        
        $closure = function ($param) use (&$test) {
            if ($param == 'override1')
            {
                $test = true;
            }
        };
    
        $pattern = 'GET /foo';
        
        // Pass the array of routes
        $a->setRoutes(array(
            $pattern => $closure,
        ));
        
        $a->setOverrideParameter($pattern, array('override1'));
    
        // Pass URL
        $a->dispatch('/foo?def');
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertTrue($test);
    }
    
    public function testOverrideOutsideAnySame()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $test = false;
    
        $closure = function ($param) use (&$test) {
            if ($param == 'override1')
            {
                $test = true;
            }
        };
    
        $pattern = 'ANY /foo';
    
        // Pass the array of routes
        $a->setRoutes(array(
            $pattern => $closure,
        ));
    
        $a->setOverrideParameter($pattern, array('override1'));
    
        // Pass URL
        $a->dispatch('/foo?def');
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertTrue($test);
    }
    
    public function testOverrideOutsideAnyDifferent()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $test = false;
    
        $closure = function ($param) use (&$test) {
            if ($param == 'override1')
            {
                $test = true;
            }
        };
    
        //$pattern = 'ANY /foo';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo' => $closure,
        ));
    
        $a->setOverrideParameter('ANY /foo', array('override1'));
    
        // Pass URL
        $a->dispatch('/foo?def');
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertTrue($test);
    }
    
    public function testOverrideOutsideAnyDifferentSwitch()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $test = false;
    
        $closure = function ($param) use (&$test) {
            if ($param == 'override1')
            {
                $test = true;
            }
        };
    
        //$pattern = 'ANY /foo';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'ANY /foo' => $closure,
        ));
    
        $a->setOverrideParameter('GET /foo', array('override1'));
    
        // Pass URL
        $a->dispatch('/foo?def');
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertTrue($test);
    }
    
    public function testOverrideOutsideAnyDifferentSwitchPriority()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $test = false;
    
        $closure = function ($param) use (&$test) {
            if ($param == 'override1')
            {
                $test = true;
            }
        };
    
        //$pattern = 'ANY /foo';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'ANY /foo' => $closure,
        ));
    
        // This is get priority
        $a->setOverrideParameter('GET /foo', array('override1'));
        // This will NOT get priority
        $a->setOverrideParameter('ANY /foo', array('override2'));
    
        // Pass URL
        $a->dispatch('/foo?def');
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertTrue($test);
    }
    
    public function testOverrideOutsideAnyDifferentSwitchDynamicPriority()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $test = false;
    
        $closure = function ($param) use (&$test) {
            if ($param == 'override1')
            {
                $test = true;
            }
        };
    
        //$pattern = 'ANY /foo';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'ANY /foo/*' => $closure,
        ));
    
        // This will get priority
        $a->setOverrideParameter('GET /foo/*', array('override1'));
        // This will NOT get priority
        $a->setOverrideParameter('ANY /foo/*', array('override2'));
    
        // Pass URL
        $a->dispatch('/foo/test?def');
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertTrue($test);
    }
    
    public function testOverrideOutsideAnyDifferentDynamicPriority()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $test = false;
    
        $closure = function ($param) use (&$test) {
            if ($param == 'override1')
            {
                $test = true;
            }
        };
    
        //$pattern = 'ANY /foo';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/*' => $closure,
        ));
    
        // This will get priority
        $a->setOverrideParameter('GET /foo/*', array('override1'));
        // This will NOT get priority
        $a->setOverrideParameter('ANY /foo/*', array('override2'));
    
        // Pass URL
        $a->dispatch('/foo/test?def');
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertTrue($test);
    }
    
    public function testOverrideOutsideAnyDifferentSwitchFail()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $test = false;
    
        $closure = function ($param) use (&$test) {
            if ($param !== 'override1')
            {
                $test = true;
            }
        };
    
        //$pattern = 'ANY /foo';
    
        // Pass the array of routes
        $a->setRoutes(array(
            'ANY /foo' => $closure,
        ));
    
        $a->setOverrideParameter('POST /foo', array('override1'));
    
        // Pass URL
        $a->dispatch('/foo?def');
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertTrue($test);
    }
    
    public function testOverrideInsideStatic()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $test = false;
    
        $closure = function ($param) use (&$test) {
            if ($param == 'override1')
            {
                $test = true;
            }
        };
    
        $pattern = 'GET /foo';
    
        // Pass the single route with an override
        $a->setRoute($pattern, $closure, array(), array('override1'));
    
        // Pass URL
        $a->dispatch('/foo?def');
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertTrue($test);
    }
    
    public function testOverrideInsideDynamic()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $test = false;
    
        $closure = function ($param) use (&$test) {
            if ($param == 'override1')
            {
                $test = true;
            }
        };
    
        $pattern = 'GET /foo/*';
    
        // Pass the single route with an override
        $a->setRoute($pattern, $closure, array(), array('override1'));
    
        // Pass URL
        $a->dispatch('/foo/bar?def');
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertTrue($test);
    }
    
    public function testOverrideArrays()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $test = false;
    
        $closure1 = function ($param) use (&$test) {
            if ($param == 'override1')
            {
                $test = true;
            }
        };
        
        $closure2 = function ($param) use (&$test) {
            if ($param == 'override2')
            {
                $test = false;
            }
        };
    
        // Pass the routes
        $a->setRoutes(array(
            'GET /foo/{int}' => $closure1,
            'GET /bar/{int}' => $closure2,
        ));
        
        // Pass the overrides
        $a->setOverrideParameters(array(
            'GET /foo/{int}' => array('override1'),
            'GET /bar/{int}' => array('override2'),
        ));
    
        // Pass URL
        $a->dispatch('/foo/4?def');
        $this->assertTrue($test);
        
        // Pass URL
        $a->dispatch('/bar/5?def');
        $this->assertFalse($test);
    }
    
    public function testWildcardSingle()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
        
        $a->setWildCardDefinition('{decimal}', '([0-9.]+)');
        
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/{decimal}' => array($class, $method),
        ));
    
        $param = '123.4';
        
        // Pass URL
        $a->map("/foo/$param?def");
    
        // Should find route
        $this->assertTrue($a->isRouteMapped());
        
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
        
        // Get the parameters
        $this->assertSame($a->getParameters(), array($param));
    }
    
    public function testWildcardMultiple()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'TestClass';
        $method = 'foo';
    
        $wilds = array(
            '{decimal}' => '([0-9.]+)',
            '{lowalpha}' => '([a-z]+)',
        );
        
        $a->setWildCardDefinitions(array(
            '{decimal}' => '([0-9.]+)',
            '{lowalpha}' => '([a-z]+)',
        ));
    
        // Pass the array of routes
        $a->setRoutes(array(
            'GET /foo/{decimal}/{lowalpha}' => array($class, $method),
        ));
    
        $param1 = '123.4';
        $param2 = 'abc';
    
        // Pass URL
        $a->map("/foo/$param1/$param2?def");
    
        // Should find route
        $this->assertTrue($a->isRouteMapped());
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array($param1, $param2));
    }

    public function testSecondaryOutside()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $test = false;
    
        $closure = function ($r, $param) use (&$test) {
            if ($param == 'secondary1')
            {
                $test = true;
            }
        };
    
        $pattern = 'GET /foo';
    
        // Pass the array of routes
        $a->setRoutes(array(
            $pattern => $closure,
        ));
    
        $a->setSecondaryParameter($pattern, array('secondary1'));
    
        // Pass URL
        $a->dispatch('/foo?def');
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertTrue($test);
    }
    
    public function testSecondaryInsideStatic()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $test = false;
    
        $closure = function ($r, $param) use (&$test) {
            if ($param == 'secondary2')
            {
                $test = true;
            }
        };
    
        $pattern = 'GET /foo';
    
        // Pass the single route with an override
        $a->setRoute($pattern, $closure, array('secondary2'));
    
        // Pass URL
        $a->dispatch('/foo?def');
    
        // Get the mapped type
        $this->assertSame($a->getMapType(), $a::C_ROUTE_FOUND);
    
        // Get the parameters
        $this->assertTrue($test);
    }

    public function testSecondaryArrays()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $test = false;
    
        $closure1 = function ($r, $param1, $param2) use (&$test) {
            if ($param2 == 'secondary1')
            {
                $test = true;
            }
        };
    
        $closure2 = function ($r, $param1, $param2) use (&$test) {
            if ($param2 == 'secondary2')
            {
                $test = false;
            }
        };
    
        // Pass the routes
        $a->setRoutes(array(
            'GET /foo/{int}' => $closure1,
            'GET /bar/{int}' => $closure2,
        ));
    
        // Pass the overrides
        $a->setSecondaryParameters(array(
            'GET /foo/{int}' => array('secondary1'),
            'ANY /bar/{int}' => array('secondary2'),
        ));
    
        // Pass URL
        $a->dispatch('/foo/4?def');
        $this->assertTrue($test);
    
        // Pass URL
        $a->dispatch('/bar/5?def');
        $this->assertFalse($test);
    }

    public function testCLI()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
        
        // Get the parameters
        $this->assertTrue($a->isCLI());
        
        global $argv;
        
        // Get the parameters
        $this->assertSame($a->getArguments(), $argv);
        
        // Get the parameter
        $this->assertSame($a->getArgument(0), $argv[0]);
    }
    
    public function testRequestMethod()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Get the parameters
        $this->assertSame($a->getRequestMethod(), 'POST');
        
        unset($_SERVER['REQUEST_METHOD']);
        
        // Get the parameters
        $this->assertSame($a->getRequestMethod(), 'GET');
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'UPDATE';
        
        // Get the parameters
        $this->assertSame($a->getRequestMethod(), 'UPDATE');
    }
    
    public function testAJAX()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
    
        // Get the parameters
        $this->assertSame($a->isAJAX(), true);
    }
    
    public function testQueryString()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $a->map('/foo?abc=123');
        
        // Get the parameters
        $this->assertSame($a->getQueryString(), 'abc=123');
        
        // Get the parameter
        $this->assertSame($a->getQuery('abc'), '123');
    }
    
    public function testNotFound()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        ob_start();
        
        // Pass URL
        @$a->dispatch('/foo?def');
        
        $page = ob_get_contents();
        
        ob_end_clean();
        
        $this->assertSame($page, 'Not Found');
    }
}

/**
 * Test Class
 *
 */
class TestClass
{
    function foo() { }

    function bar() { }
}