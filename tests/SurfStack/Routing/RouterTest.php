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
    
    public function testMap()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        // Pass the array of routes
        $a->setRoutes(array(
            '/foo' => array('SurfStack\Test\TestClass', 'foo'),
        ));
    
        // Pass URL
        $a->map('/foo?def');
        
        // Should find route
        $this->assertTrue($a->isRouteMapped());
    }
    
    public function testMapClassMethod()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'SurfStack\Test\TestClass';
        $method = 'foo';
        
        // Pass the array of routes
        $a->setRoutes(array(
            '/foo' => array($class, $method),
        ));
    
        // Pass URL
        $a->map('/foo?def');
    
        // Get the mapped class
        $this->assertSame($a->getMappedClass(), $class);
        
        // Get the mapped method
        $this->assertSame($a->getMappedMethod(), $method);
        
        // Get the parameters
        $this->assertSame($a->getParameters(), array());
    }
    
    public function testMapNoMethodDefaultIndex()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'SurfStack\Test\TestClass';
        $method = 'foo';
    
        // Pass the array of routes
        $a->setRoutes(array(
            '/foo' => array($class),
        ));
    
        // Pass URL
        $a->map('/foo?def');
    
        // Get the mapped class
        $this->assertSame($a->getMappedClass(), $class);
    
        // Get the mapped method
        $this->assertSame($a->getMappedMethod(), 'index');
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array());
    }
    
    public function testMapDynamicAction()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'SurfStack\Test\TestClass';
        $action = 'abc';
    
        // Pass the array of routes
        $a->setRoutes(array(
            '/foo/{action}' => array($class),
        ));
    
        // Pass URL
        $a->map("/foo/$action?def");
    
        // Get the mapped class
        $this->assertSame($a->getMappedClass(), $class);
        
        // Get the mapped method
        $this->assertSame($a->getMappedMethod(), $action);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array());
    }
    
    public function testMapDynamicActionWildcard()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'SurfStack\Test\TestClass';
        $action = 'abc';
        $wildcard = 'ghi123';
    
        // Pass the array of routes
        $a->setRoutes(array(
            '/foo/{action}/test/*' => array($class),
        ));
    
        // Pass URL
        $a->map("/foo/$action/test/$wildcard?def");
    
        // Get the mapped class
        $this->assertSame($a->getMappedClass(), $class);
    
        // Get the mapped method
        $this->assertSame($a->getMappedMethod(), $action);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array($wildcard));
    }
    
    public function testMapDynamicActionDoubleWildcard()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'SurfStack\Test\TestClass';
        $action = 'abc';
        $wildcard = 'ghi123/jkl456/lmnop';
    
        // Pass the array of routes
        $a->setRoutes(array(
            '/foo/{action}/test/**' => array($class),
        ));
    
        // Pass URL
        $a->map("/foo/$action/test/$wildcard?def");
    
        // Get the mapped class
        $this->assertSame($a->getMappedClass(), $class);
    
        // Get the mapped method
        $this->assertSame($a->getMappedMethod(), $action);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array($wildcard));
    }
    
    public function testMapDynamicActionWildcardString()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'SurfStack\Test\TestClass';
        $action = 'abc';
        $wildcard = 'ghi123';
        $wildstring = 'jkl';
    
        // Pass the array of routes
        $a->setRoutes(array(
            '/foo/{action}/test/*/{string}' => array($class),
        ));
    
        // Pass URL
        $a->map("/foo/$action/test/$wildcard/$wildstring?def");
    
        // Get the mapped class
        $this->assertSame($a->getMappedClass(), $class);
    
        // Get the mapped method
        $this->assertSame($a->getMappedMethod(), $action);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array($wildcard, $wildstring));
    }
    
    public function testMapDynamicWildcardActionStringProblem()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'SurfStack\Test\TestClass';
        $action = 'abc';
        $wildcard = 'ghi123';
        $wildstring = 'jkl';
    
        // Pass the array of routes
        $a->setRoutes(array(
            '/foo/*/test/{action}/{string}' => array($class),
        ));
    
        // Pass URL
        $a->map("/foo/$wildcard/test/$action/$wildstring?def");
    
        // Get the mapped class
        $this->assertSame($a->getMappedClass(), $class);
    
        // Get the WRONG mapped method
        $this->assertNotSame($a->getMappedMethod(), $action);
    
        // Get the WRONG parameters
        $this->assertNotSame($a->getParameters(), array($wildcard, $wildstring));
    }
    
    public function testMapDynamicActionWildcardStringInteger()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'SurfStack\Test\TestClass';
        $action = 'abc';
        $wildcard = 'ghi123';
        $wildstring = 'jkl';
        $wildint = '456';
    
        // Pass the array of routes
        $a->setRoutes(array(
            '/foo/{action}/test/*/{string}/{integer}' => array($class),
        ));
    
        // Pass URL
        $a->map("/foo/$action/test/$wildcard/$wildstring/$wildint?def");
    
        // Get the mapped class
        $this->assertSame($a->getMappedClass(), $class);
    
        // Get the mapped method
        $this->assertSame($a->getMappedMethod(), $action);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array($wildcard, $wildstring, $wildint));
    }
    
    public function testMapDynamicActionWildcardWildcard()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'SurfStack\Test\TestClass';
        $action = 'abc';
        $wildcard1 = 'ghi123';
        $wildcard2 = 'jkl+-_%456';
    
        // Pass the array of routes
        $a->setRoutes(array(
            '/foo/{action}/test/*/*' => array($class),
        ));
    
        // Pass URL
        $a->map("/foo/$action/test/$wildcard1/$wildcard2?def");
    
        // Get the mapped class
        $this->assertSame($a->getMappedClass(), $class);
    
        // Get the mapped method
        $this->assertSame($a->getMappedMethod(), $action);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array($wildcard1, $wildcard2));
    }
    
    public function testMapDynamicWildcardWildcard()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'SurfStack\Test\TestClass';
        $method = 'foo';
        $wildcard1 = 'ghi123';
        $wildcard2 = 'jkl+-_%456';
    
        // Pass the array of routes
        $a->setRoutes(array(
            '/foo/test/*/*' => array($class, $method),
        ));
    
        // Pass URL
        $a->map("/foo/test/$wildcard1/$wildcard2?def");
    
        // Get the mapped class
        $this->assertSame($a->getMappedClass(), $class);
    
        // Get the mapped method
        $this->assertSame($a->getMappedMethod(), $method);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array($wildcard1, $wildcard2));
    }
    
    public function testMapDynamicWildcardDoubleWildcard()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'SurfStack\Test\TestClass';
        $method = 'foo';
        $wildcard1 = 'ghi123';
        $wildcard2 = 'jkl+-_%456/a/b/c/d';
    
        // Pass the array of routes
        $a->setRoutes(array(
            '/foo/test/*/**' => array($class, $method),
        ));
    
        // Pass URL
        $a->map("/foo/test/$wildcard1/$wildcard2?def");
    
        // Get the mapped class
        $this->assertSame($a->getMappedClass(), $class);
    
        // Get the mapped method
        $this->assertSame($a->getMappedMethod(), $method);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array($wildcard1, $wildcard2));
    }
    
    public function testMapDynamicDoubleWildcardWildcard()
    {
        // Create an instance
        $a = new SurfStack\Routing\Router();
    
        $class = 'SurfStack\Test\TestClass';
        $method = 'foo';
        $wildcard1 = 'ghi123';
        $wildcard2 = 'jkl+-_%456/a/b/c/file.php';
    
        // Pass the array of routes
        $a->setRoutes(array(
            '/foo/test/**/*' => array($class, $method),
        ));
    
        // Pass URL
        $a->map("/foo/test/$wildcard1/$wildcard2?def");
    
        // Get the mapped class
        $this->assertSame($a->getMappedClass(), $class);
    
        // Get the mapped method
        $this->assertSame($a->getMappedMethod(), $method);
    
        // Get the parameters
        $this->assertSame($a->getParameters(), array('ghi123/jkl+-_%456/a/b/c', 'file.php'));
    }
    
    
}