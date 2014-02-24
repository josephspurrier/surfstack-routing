<?php

/**
 * This file is part of the SurfStack package.
 *
 * @package SurfStack
 * @copyright Copyright (C) Joseph Spurrier. All rights reserved.
 * @author Joseph Spurrier (http://josephspurrier.com)
 * @license http://www.apache.org/licenses/LICENSE-2.0.html
 */

namespace SurfStack\Routing;

/**
 * Router
 *
 * Determines the class and method a user is requesting via a URL
 */
class Router
{
    /**
     * Route is not found
     * @var int
     */
    CONST C_NOTFOUND = 10;
    
    /**
     * Route resulted in an error
     * @var int
     */
    CONST C_ERROR = 11;
    
    /**
     * Route maps to a class and method
     * @var int
     */
    CONST C_CLASSMETHOD = 12;
    
    /**
     * Route maps to a function
     * @var int
     */
    CONST C_FUNCTION = 13;
    
    /**
     * Route maps to an anonymous function
     * @var int
     */
    CONST C_ANONFUNCTION = 14;
    
    /**
     * Discovered name of the requested class
     * @var string
     */
    private $strClass = '';
    
    /**
     * Discovered name of the requested method
     * @var string
     */
    private $strMethod = '';
    
    /**
     * Discovered name of the requested function
     * @var string
     */
    private $strFunction = '';
    
    /**
     * Discovered name of the requested anonymous function
     * @var string
     */
    private $strAnonFunction = '';
    
    /**
     * Discovered parameters from URI
     * @var array
     */
    private $arrParameters = array();
    
    /**
     * Named array of routes
     * @var array
     */
    private $arrRoutes = array();
    
    /**
     * Error message if occured
     * @var string
     */
    private $strError = '';
    
    /**
     * Set the routes
     * @param array $routes
     */
    public function setRoutes(array $routes = array())
    {
        $this->arrRoutes = $routes;
    }
    
    /**
     * Maps the static route to variables
     * @param string $key
     * @param mixed $val
     * @return boolean Returns true on success, false on error
     */
    private function mapStaticRoute($key, $val)
    {
        // If the val is a string name of a function
        if (is_string($val))
        {
            // Store the function name
            $this->strFunction = $val;
        }
        // If the val is an array of class/method
        else if (is_array($val))
        {
            // Invalid size
            if (count($val) != 2)
            {
                $this->strError = "The key, $key, must have an array with 2 values: class name and method name.";
                
                return false;
            }
            
            // Store the class name
            $this->strClass = $val[0];
            
            // Store the method name
            $this->strMethod = $val[1];
        }
        // If the val is an anonymous function
        else if (is_callable($val))
        {
            // Store the function
            $this->strAnonFunction = $val;
        }
        
        return true;
    }
    
    /**
     * Maps the dynamic route to variables
     * @param string $key
     * @param string $val
     * @param array $matches
     * @param bool $foundAction
     * @param bool $foundWildcards
     * @return boolean Returns true on success, false on error
     */
    private function mapDynamicRoute($key, $val, $matches, $foundAction, $foundWildcards)
    {
        // If the val is a string name of a function
        if (is_string($val))
        {
            // Store the function name
            $this->strFunction = $val;
        }
        // If the val is an array of class/method
        else if (is_array($val))
        {
            // Invalid size
            if (!$foundAction && count($val) != 2)
            {
                $this->strError = "The key, $key, must have an array with 2 values: class name and method name.";
    
                return false;
            }
            // Invalid size
            else if ($foundAction && count($val) != 1)
            {
                $this->strError = "The key, $key, must have an array with 1 value: class name.";
    
                return false;
            }
    
            // Store the class name
            $this->strClass = $val[0];
    
            // If the route has {action} in it and MUST be first
            if ($foundAction)
            {
                // Set the method as the parameter specified by the user
                $this->strMethod=$matches[1];
            }
            else
            {
                // Store the method name
                $this->strMethod = $val[1];
            }
        }
        // If the val is an anonymous function
        else if (is_callable($val))
        {
            // Store the function
            $this->strAnonFunction = $val;
        }
    
        // If the route has {action} in it and MUST be first
        if ($foundAction)
        {
            $this->mapAction($foundWildcards, $matches);
        }
        // Else if the route doesn't have {action} in it, but has some other wildcard
        else
        {
            $this->mapNonAction($foundWildcards, $matches);
        }
    }
    
    /**
     * Extract the parameters if the {action} wildcard is used
     * @param bool $foundWildcards
     * @param array $matches
     */
    private function mapAction($foundWildcards, $matches)
    {
        // If the route has {action} AND ** in it
        if ($foundWildcards)
        {
            // This will change the behavior of the double wildcards
            // Get the remainder of the URL as an ARRAY of parameters
            //$this->arrParameters = array_filter(explode('/', $matches[2]));
        
            // Get the remainder of the URL as a SINGLE parameter
            $this->arrParameters = array($matches[2]);
        }
        // Else the route does not have a double wildcard
        else
        {
            // Set the end of the matches as the parameters
            unset($matches[0]);
            unset($matches[1]);
            $this->arrParameters = array_values($matches);
        }
    }
    
    /**
     * Extract the parameters if no {action} wildcard is used
     * @param bool $foundWildcards
     * @param array $matches
     */
    private function mapNonAction($foundWildcards, $matches)
    {
        // I realize these next statements are the same, I'm
        // just keeping them in here in case I want to change
        // the way the double wildcards work
        
        // If there are double wildcards
        if ($foundWildcards)
        {
            unset($matches[0]);
            $this->arrParameters = array_values($matches);
        }
        // Else there will be more than one wildcard and so all need to be in the parameters
        else
        {
            unset($matches[0]);
            $this->arrParameters = array_values($matches);
        }
    }

    /**
     * Map the route to a class and method
     * @param string $strURI
     */
    public function map($strURI)
    {
        // Call a method before map
        $this->beforeMap($strURI);
        
        // If a Static Route
        // If the request is in the Routes array, set the Presenter and Method,
        // but ignore the Parameters array
        if (isset($this->arrRoutes[$strURI]) || isset($this->arrRoutes[$strURI.'/']))
        {
            $this->mapStaticRoute($strURI, $this->arrRoutes[$strURI]);
        }
        // Else a Dynamic Route
        else
        {
            // Regex match
            $tokens = array(
                '{action}' => '([a-zA-Z0-9_]+)',
                '{string}' => '([a-zA-Z]+)',
                '{integer}' => '([0-9]+)',
                '*'  => '([a-zA-Z0-9-+_.%]+)',
                '**'  => '([a-zA-Z0-9-+_.%/]+)',
            );
            
            // This ensures {action} will be tested before {string}
            ksort($this->arrRoutes);

            // Loop through each route
            foreach ($this->arrRoutes as $key=>$val)
            {
                $foundAction = false;
                $foundWildcards = false;
            
                if (strpos($key, '{action}')!==false) $foundAction = true;
                if (strpos($key, '**')!==false) $foundWildcards = true;

                $pattern = strtr($key, $tokens);
                if (preg_match('@^/?'.$pattern.'/?$@', $strURI, $matches))
                {
                    $this->mapDynamicRoute($key, $val, $matches, $foundAction, $foundWildcards);
                    
                    break;
                }
            }
        }
        
        // Call a method after map
        $this->afterMap();
    }
    
    /**
     * Return the discovered class
     * @return string
     */
    public function getMappedClass()
    {
        return $this->strClass;
    }
    
    /**
     * Return the discovered method
     * @return string
     */
    public function getMappedMethod()
    {
        return $this->strMethod;
    }
    
    /**
     * Return the discovered function
     * @return string
     */
    public function getMappedFunction()
    {
        return $this->strFunction;
    }
    
    /**
     * Return the discovered anonymous function
     * @return string
     */
    public function getMappedAnonFunction()
    {
        return $this->strAnonFunction;
    }
    
    /**
     * Return the error message
     * @return string
     */
    public function getError()
    {
        return $this->strError;
    }
    
    /**
     * Return the URL parameters (if any)
     * @return array
     */
    public function getParameters()
    {
        return $this->arrParameters;
    }

    /**
     * Return the map type constant
     * @return const
     */
    public function getMapType()
    {
        if ($this->strError !== '')
        {
            return $this::C_ERROR;
        }
        else if ($this->strClass !== '' && $this->strMethod !== '')
        {
            return $this::C_CLASSMETHOD;
        }
        else if ($this->strFunction !== '')
        {
            return $this::C_FUNCTION;
        }
        else if ($this->strAnonFunction !== '')
        {
            return $this::C_ANONFUNCTION;
        }
        else
        {
            return $this::C_NOTFOUND;
        }
    }
    
    /**
     * Return if the route is mapped
     * @return bool
     */
    public function isRouteMapped()
    {
        return (($this->strClass !== '' && $this->strMethod !== '') || ($this->strFunction !== '') || $this->strAnonFunction !== '');
    }

    /**
     * Call the class method, function, or anonymous function if found
     */
    public function dispatch($strURI)
    {        
        $this->map($strURI);
        
        $this->beforeDispatch();
        
        // Call the current map type
        switch ($this->getMapType())
        {
            // Page is not found
        	case $this::C_NOTFOUND:
        	    $this->showNotFound();
    	        break;
	        // Call Class and Method 
            case $this::C_CLASSMETHOD:
                $class = $this->getMappedClass();
                $method = $this->getMappedMethod();
               
                if (!method_exists($class, $method))
                {
                    $this->strError = "The class, $class, and method, $method, cannot be found.";

                }
                else if (!is_callable(array($class, $method)))
                {
                    $this->strError = "The class, $class, and method, $method, cannot be called.";
                }
                else
                {
                    call_user_func_array(array(new $class, $method), $this->getParameters());
                }
                break;
            // Call Function
            case $this::C_FUNCTION:
                $function = $this->getMappedFunction();
               
                if (!is_callable($function))
                {
                    $this->strError = "The function, $function, cannot be found.";
                }
                else
                {
                    call_user_func_array($this->getMappedFunction(), $this->getParameters());
                }
                break;
            // Call Anonymous Function
             case $this::C_ANONFUNCTION:
                call_user_func_array($this->getMappedAnonFunction(), $this->getParameters());
                break;
        }
        
        // If an error occured
        if ($this->strError !== '')
        {
            $this->showError();
        }
        
        $this->afterDispatch();
    }
    
    /**
     * Extensible method called before mapping
     * @param string $strURI
     */
    private function beforeMap(&$strURI)
    {
        // Remove the query string
        $strURI = current(explode('?', $strURI));
    }
    
    /**
     * Extensible method called after mapping
     */
    private function afterMap()
    {
        
    }
    
    /**
     * Extensible method called before dispatching
     */
    private function beforeDispatch()
    {
        
    }
    
    /**
     * Extensible method called after dispatching
     */
    private function afterDispatch()
    {
        
    }
    
    /**
     * Extensible method called when the route is not found
     */
    public function showNotFound()
    {
        echo 'Not Found';
        header("HTTP/1.0 404 Not Found");
    }
    
    /**
     * Extensible method called when an error occurs with the routing component
     */
    public function showError()
    {        
        echo 'Error: '.$this->strError;
        header('HTTP/1.0 500 Internal Server Error');
    }
}