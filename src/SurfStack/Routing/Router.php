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
     * Route is found
     * @var string
     */
    CONST C_ROUTE_FOUND = 'Route_Found';
    
    /**
     * Route is not found
     * @var string
     */
    CONST C_ROUTE_NOT_FOUND = 'Route_Not_Found';
    
    /**
     * Function called before map()
     * @var string
     */
    CONST C_HOOK_BEFORE_MAP = 'beforeMap';
    
    /**
     * Function called after map()
     * @var string
     */
    CONST C_HOOK_AFTER_MAP = 'afterMap';
    
    /**
     * Function called before dispatch()
     * @var string
     */
    CONST C_HOOK_BEFORE_DISPATCH = 'beforeDispatch';
    
    /**
     * Function called at dispatch
     * @var string
     */
    CONST C_HOOK_DISPATCH = 'hookDispatch';
    
    /**
     * Function called after dispatch()
     * @var string
     */
    CONST C_HOOK_AFTER_DISPATCH = 'afterDispatch';
    
    /**
     * Function called during dispatch()
     * @var string
     */
    CONST C_HOOK_NOT_FOUND = 'hookNotFound';
    
    /**
     * Function called by getCallableParameters()
     * @var string
     */
    CONST C_HOOK_PARAMETER_LOGIC = 'hookParameterLogic';
    
    /**
     * Function called by getCallableRoute()
     * @var string
     */
    CONST C_HOOK_ROUTE_LOGIC = 'hookRouteLogic';
    
    /**
     * Function called by isRouteCallable()
     * @var string
     */
    CONST C_HOOK_ROUTE_VALIDATION = 'hookRouteValidation';
    
    /**
     * Discovered name of the route
     * @var string
     */
    protected $mixedRoute = false;
    
    /**
     * Discovered parameters from URI
     * @var array
     */
    protected $arrParameters = array();
    
    /**
     * Passed parameters from route list
     * @var array
     */
    protected $arrSecondaryParametersList = array();
    
    /**
     * Passed parameters for the specific route
     * @var array
     */
    protected $arrSecondaryParameters= array();
    
    /**
     * Passed parameters from route list
     * @var array
     */
    protected $arrParametersOverrideList = array();
    
    /**
     * Passed parameters for the specific route
     * @var array
     */
    protected $arrParametersOverride = array();
    
    /**
     * Named array of routes
     * @var array
     */
    protected $arrRoutes = array();
    
    /**
     * Name array of wildcards and their regex equivalent
     * @var array
     */
    protected $arrWildcardDefinitions = array();
    
    /**
     * Request URI
     * @var string
     */
    protected $strURI;
    
    /**
     * Request URI with ANY request method
     * @var string
     */
    protected $strURIAny;
    
    /**
     * Hook closures
     * @var array
     */
    protected $hooks = array();
    
    /**
     * Query String array
     * @var array
     */
    protected $arrQuery = array();
    
    /**
     * Query String
     * @var string
     */
    protected $strQuery = '';
    
    /**
     * Maps the static route to variables
     * @param string $key
     * @param mixed $val
     */
    protected function mapStaticRoute($key, $val)
    {
        // If the val is an array of class/method
        if (is_array($val) && count($val) < 1)
        {
            // Invalid size
            throw new \BadMethodCallException("The key, $key, must have an array with at least 1 value: class name.");
        }
        
        // Store the route
        $this->mixedRoute = $val;
    }
    
    /**
     * Maps the dynamic route to variables
     * @param string $key
     * @param string $val
     * @param array $matches
     * @param bool $foundAction
     * @param bool $foundWildcards
     */
    protected function mapDynamicRoute($key, $val, $matches, $foundAction, $foundWildcards)
    {        
        // If the val is an array of class/method
        if (is_array($val))
        {
            // Invalid size
            if (count($val) < 1)
            {
                throw new \BadMethodCallException("The key, $key, must have an array with at least 1 value: class name.");
            }
            
            // If the route has {action} in it and MUST be first
            if ($foundAction)
            {
                // Set the method as the parameter specified by the user                
                $this->mixedRoute = array_merge(array($val[0], $matches[1]), array_splice($val, 1));
            }
            else
            {
                $this->mixedRoute = $val;
            }
        }
        else
        {
            $this->mixedRoute = $val;
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
    protected function mapAction($foundWildcards, $matches)
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
            $this->arrParameters = array_values(array_slice($matches, 2));
        }
    }
    
    /**
     * Extract the parameters if no {action} wildcard is used
     * @param bool $foundWildcards
     * @param array $matches
     */
    protected function mapNonAction($foundWildcards, $matches)
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
     * Return the wildcard definitions
     * @return array
     */
    protected function getWildcardDefinitions()
    {
        // Merge with user definitions
        return array_merge(array(
            '{action}'  => '([a-zA-Z0-9_]+)',
            '{alpha}'   => '([a-zA-Z]+)',
            '{int}'     => '([0-9]+)',
            '*'         => '([a-zA-Z0-9-+_.%]+)',
            '**'        => '([a-zA-Z0-9-+_.%/]+)',
        ), $this->arrWildcardDefinitions);
    }
    
    /**
     * Map the URI to a callable entity
     * @param string $strURI
     */
    public function map($strURI)
    {
        // Separate out the URI and Query String
        $this->breakURI($strURI);
        
        // Call a function before map        
        $this->callHook($this::C_HOOK_BEFORE_MAP);

        // If a Static Route
        // If the URI is in the Routes array, extract the route
        if (isset($this->arrRoutes[$this->strURI]))
        {
            $this->mapStaticRoute($this->strURI, $this->arrRoutes[$this->strURI]);            

            // Extract parameters
            $this->extractParameters($this->strURI, $this->strURIAny);
        }
        // Else If the URI with ANY is in the Routes array, extract the route
        else if (isset($this->arrRoutes[$this->strURIAny]))
        {
            $this->mapStaticRoute($this->strURIAny, $this->arrRoutes[$this->strURIAny]);
            
            // Extract parameters
            $this->extractParameters($this->strURI, $this->strURIAny);
        }
        // Else a Dynamic Route
        else
        {
            // Loop through each route
            foreach ($this->arrRoutes as $key=>$val)
            {                
                // If a match is found
                if (preg_match('#^/?'.strtr($key, $this->getWildcardDefinitions()).'/?$#', $this->strURI, $matches)
                    || preg_match('#^/?'.strtr($key, $this->getWildcardDefinitions()).'/?$#', $this->strURIAny, $matches))
                {
                    // Map the route
                    $this->mapDynamicRoute($key,
                        $val,
                        $matches,
                        (strpos($key, '{action}')!==false),
                        (strpos($key, '**')!==false)
                    );
                    
                    // Extract parameters
                    $arrURI = explode(' ', $key);
                    $this->extractParameters($this->getRequestMethod().' '.$arrURI[1], 'ANY '.$arrURI[1]);
                    
                    break;
                }
            }
        }
        
        // If the route is found
        if ($this->mixedRoute !== false)
        {
            // Determine if the route is callable
            $this->isRouteCallable();
        }
        
        // Call a function after map
        $this->callHook($this::C_HOOK_AFTER_MAP);
        
        return $this;
    }
    
    /**
     * Sets override parameters if they are found
     * @param string $pattern
     */
    protected function extractParameters($pattern, $patternAny)
    {        
        // If the override parameters exist
        if (isset($this->arrParametersOverrideList[$pattern]))
        {
            // Set the override parameters
            $this->arrParametersOverride = $this->arrParametersOverrideList[$pattern];
        }
        else if (isset($this->arrParametersOverrideList[$patternAny]))
        {
            // Set the override parameters
            $this->arrParametersOverride = $this->arrParametersOverrideList[$patternAny];
        }
        
        // If the secondary parameters exist
        if (isset($this->arrSecondaryParametersList[$pattern]))
        {
            // Set the secondary parameters
            $this->arrSecondaryParameters = $this->arrSecondaryParametersList[$pattern];
        }
        else if (isset($this->arrSecondaryParametersList[$patternAny]))
        {
            // Set the secondary parameters
            $this->arrSecondaryParameters = $this->arrSecondaryParametersList[$patternAny];
        }
    }
    
    /**
     * Return the override parameters (if any)
     * @return array()
     */
    public function getOverrideParameters()
    {
        return $this->arrParametersOverride;
    }
    
    /**
     * Determine is the route is valid and callable
     */
    protected function hookRouteValidation()
    {
        // Get the raw route
        $route = $this->getRoute();
        
        // If the route is not callable
        if (!is_callable($route))
        {
            // If a class method
            if (is_array($route))
            {
                $class = (isset($route[0]) ? $route[0] : 'MISSING');
                $method = (isset($route[1]) ? $route[1] : 'MISSING');
                
                // If array contains more than 2 values (not callable)
                if (count($route) > 2)
                {
                    throw new \BadMethodCallException("The class, $class, and method, $method, cannot be called (more than 2 values in array).");
                }
                // Else if a class or method is missing
                else 
                {
                    throw new \BadMethodCallException("The class, $class, and method, $method, cannot be called.");
                }
            }
            // Else if a string
            else if (is_string($route))
            {
                throw new \BadFunctionCallException("The function, $route, cannot be called.");
            }
            // Else is unsupported
            else
            {
                throw new \UnexpectedValueException('The route type, '.gettype($route).', is not supported.');
            }
        }
    }
    
    /**
     * Determine if the route is callable
     */
    protected function isRouteCallable()
    {
        return $this->callHook($this::C_HOOK_ROUTE_VALIDATION);
    }
    
    /**
     * Return the discovered route
     * @return mixed (Array for class method, string for function, or closure)
     */
    public function getRoute()
    {
        return $this->mixedRoute;
    }
    
    /**
     * Return the route entity
     * @return mixed
     */
    protected function hookRouteLogic()
    {
        // Get the raw route
        $route = $this->getRoute();
        
        // If the route contains two items and is a callable class method
        if (is_array($route)
        && count($route) == 2
        && method_exists($route[0], $route[1])
        && is_string($route[0]))
        {
            // Return a usable array for static and non-static methods
            return array(new $route[0], $route[1]);
        }
        // Else just return the item
        else
        {
            return $route;
        }
    }
    
    /**
     * Return the discovered route that can be used with call_user_func_array()
     * @return mixed (Array for class method, string for function, or closure)
     */
    public function getCallableRoute()
    {
        return $this->callHook($this::C_HOOK_ROUTE_LOGIC);
    }

    /**
     * Return the map type constant
     * @return const Returns C_ROUTE_FOUND or C_ROUTE_NOT_FOUND
     */
    public function getMapType()
    {
        if ($this->isRouteMapped())
        {
            return $this::C_ROUTE_FOUND;
        }
        else
        {
            return $this::C_ROUTE_NOT_FOUND;
        }
    }
    
    /**
     * Return if the route is mapped and callable
     * @return bool
     */
    public function isRouteMapped()
    {
        return ($this->mixedRoute !== false);
    }

    /**
     * Call the class method, function, or closure if found
     */
    public function dispatch($strURI)
    {        
        $this->map($strURI);

        // Call a function before dispatch
        $this->callHook($this::C_HOOK_BEFORE_DISPATCH);
        
        // Call the current map type
        switch ($this->getMapType())
        {
            // Call the entity
            case $this::C_ROUTE_FOUND:
                $this->callHook($this::C_HOOK_DISPATCH);
            break;
            // Page is not found
            case $this::C_ROUTE_NOT_FOUND:
                $this->callHook($this::C_HOOK_NOT_FOUND);
                break;
        }
        
        // Call a function after dispatch
        $this->callHook($this::C_HOOK_AFTER_DISPATCH);
    }
    
    /**
     * Calls the route
     */
    protected function hookDispatch()
    {
        call_user_func_array($this->getCallableRoute(), $this->getCallableParameters());
    }
    
    /**
     * Set a wildcard and it's regex equivalent
     * @param string $wildcard String like '{alphanum}'
     * @param string $regex String like '([a-zA-Z0-9]+)'
     */
    public function setWildCardDefinition($wildcard, $regex)
    {
        $this->arrWildcardDefinitions[$wildcard] = $regex;
        
        return $this;
    }
    
    /**
     * Sets the secondary collection of wildcards and the regex equivalents
     * Does not replace five defaults
     * @param array Array of wildcard => regex 
     */
    public function setWildCardDefinitions(array $arrWildcardDefinitions)
    {
        $this->arrWildcardDefinitions = $arrWildcardDefinitions;
        
        return $this;
    }
    
    /**
     * Set a route
     * @param string $pattern Example 'GET /foo' or 'POST /bar'
     * @param mixed $route (Array for class method, string for function, or closure)
     * @param array $arrOverrideParameters Array of objects or values to pass
     */
    public function setRoute($pattern, $route,
        array $arrSecondaryParameters = array(),
        array $arrOverrideParameters = array())
    {        
        // Store the route
        $this->arrRoutes[$pattern] = $route;
        
        // If the secondary parameters are set
        if ($arrSecondaryParameters)
        {
            // Store them with the same key
            $this->arrSecondaryParametersList[$pattern] = $arrSecondaryParameters;
        }
        
        // If the override parameters are set
        if ($arrOverrideParameters)
        {
            // Store them with the same key
            $this->arrParametersOverrideList[$pattern] = $arrOverrideParameters;
        }
        
        return $this;
    }
    
    /**
     * Set the override parameters for a route
     * @param string $pattern Example 'GET /foo' or 'POST /bar'
     * @param array $arrParameters Array of objects or values to pass
     */
    public function setOverrideParameter($pattern, array $arrParameters)
    {
        // Store them with the same key
        $this->arrParametersOverrideList[$pattern] = $arrParameters;
        
        return $this;
    }
    
    /**
     * Set the override parameters that will be passed to the routes
     * as indexed arrays
     * @param array $arrParameters Array of pattern => arrParameters
     */
    public function setOverrideParameters(array $arrParameters)
    {
        $this->arrParametersOverrideList = $arrParameters;
        
        return $this;
    }
    
    /**
     * Set a secondary parameter for a route
     * @param string $pattern Example 'GET /foo' or 'POST /bar'
     * @param array $arrParameters Array of objects or values to pass
     */
    public function setSecondaryParameter($pattern, array $arrParameters)
    {
        $this->arrSecondaryParametersList[$pattern] = $arrParameters;
        
        return $this;
    }
    
    /**
     * Set the secondary parameters that will be passed to the routes
     * as indexed arrays
     * @param array $arrParameters Array of pattern => arrParameters
     */
    public function setSecondaryParameters(array $arrParameters)
    {
        $this->arrSecondaryParametersList = $arrParameters;
        
        return $this;
    }
    
    /**
     * Set the routes
     * @param array $arrRoutes
     */
    public function setRoutes(array $arrRoutes = array())
    {
        $this->arrRoutes = $arrRoutes;
        
        return $this;
    }
    
    /**
     * Store the URI and Query String
     * @param string $strURI
     */
    protected function breakURI($strURI)
    {
        $arr = explode('?', $strURI);
        
        // Remove the query string
        $this->strURI = $this->getRequestMethod().' '.$arr[0];
        
        // Create an ANY wildcard
        $this->strURIAny = 'ANY '.current(explode('?', $strURI));
        
        // Store the query string
        $this->strQuery = (isset($arr[1]) ? $arr[1] : (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : ''));
        
        // Parse the query string
        parse_str($this->strQuery, $this->arrQuery);
    }
    
    /**
     * Return the parameter logic
     * @return array
     */
    protected function hookParameterLogic()
    {
        // If the override parameters are set
        if ($this->getOverrideParameters())
        {
            // Return them
            return array_merge($this->getOverrideParameters());
        }
        // Else
        else
        {
            // Merge the parameters and secondary parameters
            return array_merge(array($this), array_merge($this->getParameters(), $this->getSecondaryParameters()));
        }
    }
    
    /**
     * Return the parameters prepared for the callable entity
     * @return array
     */
    public function getCallableParameters()
    {
        return $this->callHook($this::C_HOOK_PARAMETER_LOGIC);
    }
    
    /**
     * Return the URL parameters (if any)
     * @return array
     */
    public function getParameters()
    {        
        // Return standard parameters
        return $this->arrParameters;
    }
    
    /**
     * Return the secondary parameters (if any)
     * @return array()
     */
    public function getSecondaryParameters()
    {
        return $this->arrSecondaryParameters;
    }
    
    /**
     * Return the URL parameter from array or returns $default
     * Uses the getCallableParameters() array
     * @param int $int
     * @param mixed $default
     * @return mixed
     */
    public function getParameter($int, $default = null)
    {
        $arr = $this->getCallableParameters();
        
        // Return the parameters
        return (isset($arr[$int]) ? $arr[$int] : $default);
    }
    
    /**
     * Return the CLI arguments
     * @return array()
     */
    public function getArguments()
    {        
        return (isset($_SERVER['argv']) ? $_SERVER['argv'] : array());
    }
    
    /**
     * Return the CLI argument from array or returns $default
     * @param int $int
     * @param mixed $default
     * @return mixed
     */
    public function getArgument($int, $default = null)
    {
        $arr = $this->getArguments();
        
        // Return the arguments
        return (isset($arr[$int]) ? $arr[$int] : $default);
    }
    
    /**
     * Returns the request method (GET, HEAD, POST, PUT, DELETE, OPTIONS,
     * PATCH, TRACE, CONNECT) or the X-HTTP-METHOD-OVERRIDE if it's a POST
     * request
     * 
     *  Headers will send: X-HTTP-METHOD-OVERRIDE
     * $_SERVER will show: HTTP_X_HTTP_METHOD_OVERRIDE
     * 
     * @return string
     */
    public function getRequestMethod()
    {
        // Get the request method
        $requestMethod = strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
        
        // Set the request method to the method override if it exists
        return strtoupper($requestMethod == 'POST' && isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])
            ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']
            : $requestMethod
        );
    }
    
    /**
     * Is the request from a command line?
     */
    public function isCLI()
    {
        return (php_sapi_name() == 'cli');
    }
    
    /**
     * Is the request an AJAX call?
     * @return boolean
     */
    public function isAJAX()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }
    
    /**
     * Is the request a POST?
     * @return boolean
     */
    public function isPOST()
    {
        return ($this->getRequestMethod() == 'POST');
    }
    
    /**
     * Is the request a GET?
     * @return boolean
     */
    public function isGET()
    {
        return ($this->getRequestMethod() == 'GET');
    }
    
    /**
     * Is the request a HEAD?
     * @return boolean
     */
    public function isHEAD()
    {
        return ($this->getRequestMethod() == 'HEAD');
    }
    
    /**
     * Is the request a PUT?
     * @return boolean
     */
    public function isPUT()
    {
        return ($this->getRequestMethod() == 'PUT');
    }
    
    /**
     * Is the request a DELETE?
     * @return boolean
     */
    public function isDELETE()
    {
        return ($this->getRequestMethod() == 'DELETE');
    }
    
    /**
     * Is the request an OPTIONS?
     * @return boolean
     */
    public function isOPTIONS()
    {
        return ($this->getRequestMethod() == 'OPTIONS');
    }
    
    /**
     * Is the request a PATCH?
     * @return boolean
     */
    public function isPATCH()
    {
        return ($this->getRequestMethod() == 'PATCH');
    }
    
    /**
     * Is the request a TRACE?
     * @return boolean
     */
    public function isTRACE()
    {
        return ($this->getRequestMethod() == 'TRACE');
    }
    
    /**
     * Is the request a CONNECT?
     * @return boolean
     */
    public function isCONNECT()
    {
        return ($this->getRequestMethod() == 'CONNECT');
    }
    
    /**
     * Return the URL query string
     * @return array
     */
    public function getQueryString()
    {
        return $this->strQuery;
    }
    
    /**
     * Return the query string parameter from array by key
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getQuery($key, $default = null)
    {
        return (isset($this->arrQuery[$key]) ? $this->arrQuery[$key] : $default);
    }
    
    /**
     * Set a route that runs at a specific time
     * 
     * Called route will be passed a reference of current class
     * 
     * @param constant $name Name of the hook (C_HOOK_BEFORE_MAP, C_HOOK_AFTER_MAP,
     * C_HOOK_BEFORE_DISPATCH, C_HOOK_AFTER_DISPATCH, C_HOOK_NOT_FOUND)
     * @param mixed (Array for class method, string for function, or closure)
     */
    public function setHook($name, $route)
    {        
        $this->hooks[$name] = $route;
        
        return $this;
    }
    
    /**
     * Sets an array of hooks that run at a specific time
     *
     * Called route will be passed a reference of current class
     *
     * @param constant $name Name of the hook (C_HOOK_BEFORE_MAP, C_HOOK_AFTER_MAP,
     * C_HOOK_BEFORE_DISPATCH, C_HOOK_AFTER_DISPATCH, C_HOOK_NOT_FOUND)
     * @param mixed (Array for class method, string for function, or closure)
     */
    public function setHooks(array $arrHooks)
    {
        $this->hooks = $arrHooks;
        
        return $this;
    }
    
    /**
     * Call the hook
     * @param string $name Name of hook
     */
    protected function callHook($name)
    {
        // If the hook exists
        if (isset($this->hooks[$name]))
        {
            // If the hook is callable
            if (is_callable($this->hooks[$name]))
            {
                // Call the hook
                return call_user_func_array($this->hooks[$name], array(&$this));
            }
            // Else the hook is not callable
            else
            {
                // Set the error message
                throw new \BadFunctionCallException("The hook, $name, is not callable.");
            }
        }
        // Else if the class method exists
        else if (is_callable(array($this, $name)))
        {
            // Call the class method
            return call_user_func_array(array($this, $name), array(&$this));
        }
    }
    
    /**
     * Display not found message (HTTP Code 404)
     */
    protected function hookNotFound()
    {
        header('HTTP/1.0 404 Not Found');
        echo 'Not Found';
    }
}
