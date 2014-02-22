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
     * Set the routes
     * @param array $routes
     */
    public function setRoutes(array $routes = array())
    {
        $this->arrRoutes = $routes;
    }
    
    /**
     * Map the route to a class and method
     * @param string $strURI
     */
    public function map($strURI)
    {
        // Remove the query string
        $strURI = current(explode('?', $strURI));
        
        // If a Static Route
        // If the request is in the Routes array, set the Presenter and Method,
        // but ignore the Parameters array
        if (isset($this->arrRoutes[$strURI]) || isset($this->arrRoutes[$strURI.'/']))
        {
            $this->strClass = $this->arrRoutes[$strURI][0];
            $this->strMethod = (count($this->arrRoutes[$strURI]) == 1 ? 'index' : $this->arrRoutes[$strURI][1]);
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
                    // If the request array is empty, use Automatic Routing
                    if (count($val)==0)
                    {
                        $finalRoute = $this->extractRoute($matches[1], $finalRoute);
                        break;
                    }
                    // Else assign the value of the Presenter
                    else $this->strClass = $val[0];
            
                    // If the route has {action} in it and MUST be first
                    if ($foundAction)
                    {                        
                        // Set the method as the parameter specified by the user   
                        $this->strMethod=$matches[1];
            
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
                    // Else if the route doesn't have {action} in it, but has some other wildcard
                    else
                    {
                        // If the array is missing the second argument, set it to the default index() method
                        $this->strMethod = (count($val) == 1 ? 'index' : $val[1]);
            
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
                    
                    break;
                }
            }
        }
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
     * Return the URL parameters (if any)
     * @return array
     */
    public function getParameters()
    {
        return $this->arrParameters;
    }

    /**
     * Return if the route is mapped
     * @return bool
     */
    public function isRouteMapped()
    {
        return ($this->strClass != '' && $this->strMethod != '');
    }
}