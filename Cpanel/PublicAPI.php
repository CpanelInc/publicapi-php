<?php
/**
 * Cpanel_PublicAPI
 * 
 * Copyright (c) 2011, cPanel, Inc.
 * All rights reserved.
 * http://cpanel.net
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * Neither the name of cPanel, Inc. nor the
 *      names of its contributors may be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA,
 * OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * @category  Cpanel
 * @package   Cpanel_PublicAPI
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, Inc., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.1.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
/**
 * PublicAPI client class.
 * 
 * The PublicAPI client class provides a PHP class for the PublicAPI interface,
 * which is used by other API client classes in other languages, but this client
 * also incorporates the legacy cPanel PHP client classes, namely LivePHP and
 * XML-API. 
 *
 * @class     Cpanel_PublicAPI
 * @category  Cpanel
 * @package   Cpanel_PublicAPI
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, Inc., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.1.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
class Cpanel_PublicAPI extends Cpanel_Core_Object
{
    /**
     * Singleton storage
     * @var Cpanel_PublicAPI
     */
    private static $_instance = null;
    /**
     * Utility to help enforce Singleton pattern, despite the public constructor
     * @var bool
     */
    private static $_canInstantiate = false;
    /**
     * Storage containter for factory objects
     * @var mixed
     */
    private $_registry = null;
    /**
     * The class is a Singleton.  The constructor, however is public due to
     * inheritance from Cpanel_Core_Object.
     * 
     * @param arrays $optsArray Option configuration data
     * 
     * @return Cpanel_PublicAPI  
     * @throws Exception If      instantiated directly. {@link getInstance()}
     */
    public function __construct($optsArray = false)
    {
        // Force the use of getInstance
        if (self::$_canInstantiate !== true) {
            throw new Exception(
                __CLASS__ . ' must be instantiated with '
                . __CLASS__ . '::getInstance().'
            );
        }
        if (!is_array($optsArray)) {
            $optsArray = array(
                'cpanel' => array()
            );
        } elseif (!array_key_exists('cpanel', $optsArray)) {
            $optsArray = array(
                'cpanel' => $optsArray
            );
        }
        parent::__construct($optsArray);
        // TODO decouple into config option
        // Handle timezone
        $tz = ini_get('date.timezone');
        $tz = ($tz) ? $tz : 'UTC';
        date_default_timezone_set($tz);
        // TODO: refactor for dynamic loading and custom loading
        // Attach listner
        Cpanel_Listner_Observer_GenericLogger::initLogger(
            $this,
            1,
            array('level' => 'std')
        );
        // Create registry
        $this->registerRegistry();
        return $this;
    }
    /**
     * Enforce singleton; no cloning; php >=5.0
     * 
     * @return void     
     * @throws Exception If class is cloned.
     */
    final public function __clone()
    {
        throw new Exception(__CLASS__ . ' can not be cloned.');
    }
    /**
     * Get the Singleton instance, creating it as necessary.
     * 
     * @param array $optsArray Optional configuration data
     * 
     * @return Cpanel_PublicAPI
     */
    public static function getInstance($optsArray = array())
    {
        if (self::$_instance === null) {
            self::$_canInstantiate = true;
            self::$_instance = new self($optsArray);
            self::$_canInstantiate = false;
        }
        return self::$_instance;
    }
    /**
     * Obliterate all internals
     * 
     * Useful for enforcing a known state. It is extremely rare, outside of unit
     * testing, that this method is used.
     * 
     * @return void
     */
    public static function resetInstance()
    {
        if (self::$_instance !== null) {
            self::$_instance = null;
        }
    }
    /**
     * Create the internal registry for storing factory objects.
     * 
     * NOTE: Any class outside the PHP builtin space or the Cpanel package space
     * will need to have been previously included/required or be available via
     * previously defined autoloader.  The class specificed should be or 
     * inherit from ArrayObject.
     *
     * @param string $registryClass Name of object class to instantiate 
     * 
     * @return Cpanel_PublicAPI
     * @throws Exception If $registryClass does not inherit ArrayObject
     */
    protected function registerRegistry($registryClass = '')
    {
        if (empty($registryClass)) {
            //look at config
            $registryClass = $this->cpanel->package;
            if (!empty($registryClass)) {
                $registryClass = $this->cpanel->package->registryclass;
            }
        }
        if (empty($registryClass)) {
            $this->_registry = new ArrayObject(array());
        } elseif ($registryClass != 'disabled') {
            $reg = new $classname();
            if (!($reg instanceof ArrayObject)) {
                throw new Exception(
                    'Registries must be or extend ArrayObject class'
                );
            }
            $this->_registry = $reg;
        }
        return $this;
    }
    /**
     * Return internal factory object registry.
     * 
     * @return ArrayObject|mixed
     */
    protected function getRegistry()
    {
        return self::getInstance()->_registry;
    }
    /**
     * Retrieve the Service namespace from given array or config, if one exists
     * 
     * If the Cpanel config convention is utilized, a successful return will
     * provide a Cpanel_Core_Object which should have a key/namespace
     * 'config'. If the $type is not found, the general service namespace will 
     * be returned (which may also have the 'config' namespace).
     * 
     * @param string             $type      The named service type to retrieve
     * @param Cpanel_Core_Object $optsArray Cpanel config to search within
     * 
     * @return Cpanel_Core_Object|array Emtpy array if $type or $optsArray is
     *  empty, otherwise the expected configuration data 
     */
    public static function getServiceConfig($type, $optsArray)
    {
        $opts = $optsArray;
        // return empty array if called without 'blank' $type or $optsArray
        // due to inherent complexity with configs, this makes customized configs
        //  easier to deal with
        if (empty($type) || empty($opts)) {
            return array();
        }
        //convert to Cpanel_Core_Object if not already
        if (!($opts instanceof Cpanel_Core_Object)) {
            $opts = new Cpanel_Core_Object($opts);
        }
        //traverse into 'cpanel' namespace if present
        $base = $opts->getOption('cpanel');
        if (!empty($base)) {
            $opts = $base;
        }
        // traverse into 'service' namespace if present
        $base = $opts->getOption('service');
        if (!empty($base)) {
            $opts = $base;
        }
        //source out requested $type namespace
        $base = $opts->getOption($type);
        // if requested $type wasn't found, return the default service namespace
        if (!empty($base)) {
            $opts = $base;
        }
        return $opts;
    }
    /**
     * Used to extract config values from a specific namespace
     * 
     * @param string             $name      Namespace to seek config values for
     * @param Cpanel_Core_Object $optsArray Config to seek within
     * 
     * @return Cpanel_Core_Object|array Empty array if $optsArray is 
     *  empty or $name do not exist if config.
     */
    public static function getNamedConfig($name, $optsArray)
    {
        $opts = $optsArray;
        if (empty($opts)) {
            return array();
        }
        $baseNamed = $opts->$name;
        if (empty($baseNamed)) {
            return array();
        } else {
            $opts = $baseNamed;
        }
        $base = $opts->config;
        if (!empty($base)) {
            return $base;
        } else {
            return $baseNamed;
        }
    }
    /**
     * Recursively merge two arrays.
     * 
     * @param array $arr1 base array, existing key/values will be overwritten 
     * @param array $arr2 array of new values
     * 
     * @static
     * 
     * @return array A new array contain all values of the two arrays, keys in
     *  second array take precedent
     */
    public static function mergeConfigs($arr1, $arr2)
    {
        foreach ($arr2 as $key => $value) {
            if (array_key_exists($key, $arr1) && is_array($value) && is_array($arr1[$key])) {
                $arr1[$key] = self::mergeConfigs($arr1[$key], $arr2[$key]);
            } else {
                $arr1[$key] = $value;
            }
        }
        return $arr1;
    }
    /**
     * Return an aggregated config based on passed config & stored config values
     * 
     * @param string $type      Service type
     * @param arrays $optsArray Additional config to parse/merge
     * @param string $name      Specific namespace to seek
     * 
     * @return array Aggregated config
     */
    private static function _getAggregateConfig($type, $optsArray, $name)
    {
        $self = self::getInstance();
        $storedServicesConfig = self::getServiceConfig($type, $self->cpanel->service);
        $workingConfig = self::getServiceConfig($type, $optsArray);
        //TODO: decouple and thoroughly test deeply nested configs
        if (!empty($name)) {
            $workingConfigNamed = $self->getNamedConfig($name, $workingConfig);

            if (count($workingConfigNamed)) {
                if ($workingConfig->config && count($workingConfig->config)) {
                    //must try to merge
                    $workingConfig = self::mergeConfigs(
                        $workingConfig->config->getAllDataRecursively(),
                        $workingConfigNamed->getAllDataRecursively()
                    );
                } else {
                    $workingConfig = $workingConfigNamed->getAllDataRecursively();
                }
            }
            $storedNamedConfig = $self->getNamedConfig($name, $storedServicesConfig);
            if ($storedServicesConfig instanceof Cpanel_Core_Object
                && count($storedServicesConfig->config)
            ) {
                $storedConfig = self::mergeConfigs(
                    $storedServicesConfig->config->getAllDataRecursively(),
                    $storedNamedConfig->getAllDataRecursively()
                );
            } elseif ($storedNamedConfig instanceof Cpanel_Core_Object) {
                $storedConfig = $storedNamedConfig->getAllDataRecursively();
            } else {
                $storedConfig = $storedNamedConfig;
            }
        } else {
            if ($storedServicesConfig instanceof Cpanel_Core_Object
                && $storedServicesConfig->config
            ) {
                if (count($storedServicesConfig) > 1) {
                    $default = $storedServicesConfig->getAllDataRecursively();
                    unset($default['config']);
                    $storedConfig = self::mergeConfigs(
                        $default,
                        $storedServicesConfig->config->getAllDataRecursively()
                    );
                } else {
                    $storedConfig = $storedServicesConfig->config->getAllDataRecursively();
                }
            } elseif ($storedServicesConfig instanceof Cpanel_Core_Object) {
                $storedConfig = $storedServicesConfig->getAllDataRecursively();
            } else {
                $storedConfig = $storedServicesConfig;
            }
        }
        $aggConfig = self::mergeConfigs($storedConfig, $workingConfig);
        return $aggConfig;
    }
    /**
     * Factory method used to return Service object.
     *
     * @param string $type      Service  type
     * @param array  $optsArray Service  object configuration data
     * @param string $name      Specific name for retrieving a predefine service
     *  setup or stored instance thereof
     * 
     * @return mixed      A Cpanel_Service object
     * @throws Exception If invalid service type is requested.
     */
    public static function factory($type, $optsArray = array(), $name = '')
    {
        if (empty($type) || !is_string($type)) {
            throw new exception(
                'Cpanel::factory requires 1st arguement to be non-empty STRING'
            );
        } else {
            $type = strtolower($type);
        }
        $self = self::getInstance();
        $tmpName = (empty($name)) ? "" : "_$name";
        // Attempt to find a stored reference and return it
        $reg = $self->getRegistry();
        if (($reg !== null)
            && (is_string($tmpName))
            && ($reg->offsetExists($type . $tmpName))
            && ($reg->offsetGet($type . $tmpName) !== false)
        ) {
            return $self->getRegistry()->offsetGet($type . $tmpName);
        }
        
        // No valid object found: determine class name and instantiate
        $classname;
        $storageName;
        switch ($type) {
        case 'whm':
        case 'whostmgr':
        case 'xmlapi':
        case 'jsonapi':
            $storageName = 'whm';
            $classname = 'Cpanel_Service_WHM';
            break;

        case 'cpanel':
        case 'livephp':
        case 'live':
            $storageName = 'cpanel';
            $classname = 'Cpanel_Service_cPanel';
            break;
            // TODO: custom service loading
            
        }
    
        if (!isset($classname) || !class_exists($classname)) {
            throw new Exception("Invalid service: {$type}");
        }
        
        // by change, the same service may be stored under the normalized name
        if (($reg !== null && isset($storageName))
            && (is_string($tmpName))
            && ($reg->offsetExists($storageName . $tmpName))
            && ($reg->offsetGet($storageName . $tmpName) !== false)
        ) {
            return $self->getRegistry()->offsetGet($storageName . $tmpName);
        }
        
        
        if (empty($optsArray)) {
            $optsArray = array();
        }
        if (!array_key_exists('listner', $optsArray)
            && $self->getOption('listner')
        ) {
            $optsArray['listner'] = $self->listner;
        }
        $aggConfig = self::_getAggregateConfig($type, $optsArray, $name);
        
        $newobj = new $classname($aggConfig);
        // if registry, store
        if ($reg !== null) {
            if (isset($storageName) && $storageName != $type) {
                $type = $storageName;
            }
            $reg->offsetSet($type . $tmpName, $newobj);
        }
        return $newobj;
    }
    /**
     * Direct http URL query
     * 
     * @param string $service  cPanel service to use
     * @param string $uri      URI to fetch
     * @param string $method   HTTP method
     * @param mixed  $formdata Associative array or paramter string representing
     *  form data to send
     * @param array  $headers  Associative array of custom header to add to http
     *  request
     * 
     * @return Cpanel_Query_Object
     */
    public function api_request($service, $uri, $method = 'GET', $formdata = '', $headers = '')
    {
        $obj = self::factory($service);
        if (!method_exists($obj, 'directURLQuery')) {
            throw new Exception("$service does not support direct URL queries");
        }
        $optsArray = array(
            'httpQueryType' => strtoupper($method),
            'httpHeaders' => $headers,
        );
        return $obj->directURLQuery($uri, $formdata, $optsArray);
    }
    /**
     * Works as dispatch
     * 
     * Coordinates with the {@link factory()} to return and instance when 
     * "get{$service}" is invoked.  Otherwise will take the "api" query method
     * and pass in to an appropriate service object.
     *
     * @param string $method Method name as originally invoked
     * @param array  $args   Method arguments from original call
     * 
     * @return Cpanel_Query_Object
     * @throws Exception If service cannot be deduced from "api" query or
     *  original method call was not a "get" or "api" based invocation.
     */
    public function __call($method, $args)
    {
        if (strpos($method, 'get') === 0) {
            //attempt to factory an item
            $type = substr($method, 3);
            $factargs = (count($args) >= 1 && is_array($args[0])) ? $args[0] : array();
            $name = (is_array($factargs) && array_key_exists('name', $factargs)) ? $factargs['name'] : '';
            return self::factory($type, $factargs, $name);
        } elseif (strpos($method, '_api') !== false) {
            //invoking the service, ie "whm_api", "cpanel_api1_request", etc.
            $type = substr($method, 0, strpos($method, '_'));
            $obj = self::factory($type);
            if (empty($args) || (is_array($args) && count($args) == 0)) {
                throw new Exception(
                    'Service API call requires at least one parameter'
                );
            }
            if ($type == 'whm') {
                $func = $args[0];
                if (is_array($args) && count($args) == 1) {
                    $args_to_pass = array();
                } else {
                    $args_to_pass = $args[1];
                }
            } elseif ($type == 'cpanel') {
                $func = substr($method, (strpos($method, '_') + 1));
                $args_to_pass = $args;
            } else {
                throw new Exception("Invalid Service: {$type}");
            }
            return call_user_func_array(
                array(
                    $obj,
                    $func
                ),
                $args_to_pass
            );
        }
        throw new Exception("Invalid method: {$method}");
    }
}
?>