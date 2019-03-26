<?php
/**
 * Cpanel_Service_Abstract
 * 
 * Copyright (c) 2011, cPanel, L.L.C.
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
 *    * Neither the name of cPanel, L.L.C. nor the
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
 * @package   Cpanel_Service
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.1.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
*/
/**
 * Abstract class for Service classes.
 * 
 * @class     Cpanel_Service_Abstract
 * @category  Cpanel
 * @package   Cpanel_Service
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.1.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
abstract class Cpanel_Service_Abstract extends Cpanel_Core_Object
{
    /**
     * Adapter storage
     * @var array
     */
    protected $adapters = array();
    /**
     * List of diabled adapters
     * @var array
     */
    protected $disabledAdapters = array();
    /**
     * Type of array that API1 calls must adhere to 
     */
    const API1ARGS = 'ordinal';
    /**
     * Type of array that API2 calls must adhere to 
     */
    const API2ARGS = 'associative';
    /**
     * Constant representing the cPanel service
     */
    const ADAPTER_CPANEL = 'cpanel';
    /**
     * Constant representing the WHM service
     */
    const ADAPTER_WHM = 'whostmgr';
    /**
     * Constant representing the LivePHP service
     */
    const ADAPTER_LIVE = 'live';
    /**
     * Constructor
     * 
     * @param array $optsArray Option configuration data
     * 
     * @return Cpanel_Service_Abstract
     */
    public function __construct($optsArray = array())
    {
        parent::__construct($optsArray);
        if (!$this->listner) {
            Cpanel_Listner_Observer_GenericLogger::initLogger(
                $this,
                1,
                array('level' => 'std')
            );
        }
        return $this;
    }
    /**
     * For use by extending classes to retreive their default adapter name value
     * 
     * The method is specifically here to get around late static binding, which 
     * is only available in 5.3
     * 
     * @return string
     */
    abstract public function getDefaultAdapterName();
    /**
     * Mark a particular adapter as disabled so that it cannot be explicitly or
     * implicitly used by the Service object instance
     * 
     * @param string $type Adapter name to disable
     * 
     * @return Cpanel_Service_Abstract
     * @throws Exception If $type is invalid
     */
    public function disableAdapter($type)
    {
        $type = $this->validAdapter($type);
        if (!$type) {
            // TODO: consider raising warning instead
            throw new Exception('Invalid adapter type');
        } elseif (!in_array($type, $this->disabledAdapters)) {
            $this->disabledAdapters[] = $type;
        }
        return $this;
    }
    /**
     * Enable a previously disabled adapter with the Service object instance
     * 
     * @param string $type Adapter name to enable
     * 
     * @return Cpanel_Service_Abstract
     * @throws Exception If $type is invalid
     */
    public function enableAdapter($type)
    {
        $type = $this->validAdapter($type);
        if (!$type) {
            // TODO: consider raising warning instead
            throw new Exception('Invalid adapter type');
        }
        $key = array_search($type, $this->disabledAdapters);
        if ($key !== false) {
            unset($this->disabledAdapters[$key]);
        }
        return $this;
    }
    /**
     * Initialize key parameters of a RemoteQuery based adpater
     * 
     * This will attempt to set host, user, and authentication information, as
     * well as port and protocol. User and authentication information may 
     * retrieved from the environment context if that feature is enabled.
     * 
     * @param Cpanel_Query_Http_Abstract $adapter Adapter to initialize
     * 
     * @return Cpanel_Query_Http_Abstract Initialized adapter
     */
    protected function initAdapter(Cpanel_Query_Http_Abstract $adapter)
    {
        $vars['host'] = $this->getOption('host');
        $vars['user'] = $this->getOption('user');
        if (!($vars['hash'] = $this->getOption('hash'))) {
            $vars['password'] = $this->getOption('password');
        } else {
            $vars['password'] = null;
        }
        $vars['port'] = $this->getOption('port');
        $vars['protocol'] = $this->getOption('protocol');
        if (!$this->disableEnvironmentContext) {
            $vars = $this->_getEnvironmentContext($vars);
        }
        $adapter->init($vars['host'], $vars['user'], $vars['password']);
        if (!empty($vars['hash'])) {
            $adapter->setHash($vars['hash']);
        }
        if ($vars['port']) {
            $adapter->setPort($vars['port']);
        } elseif ($vars['protocol']) {
            $adapter->setProtocol($vars['protocol']);
        }
        return $adapter;
    }
    /**
     * Fills an array, as necessary, with key/values pairs associated with the
     * initialization process based on the processes effective user.
     * 
     * Will set the following key/value pairs if the input array has an empty or
     * undefined pair:
     * host => 127.0.0.1
     * user => The effective user running the script
     * password => The password located in the script's environment (not $_ENV),
     *  if available
     * hash => Stored access hash value, if available
     * 
     * NOTE: because of the way {@link _getEUIDAuth} works, either hash or 
     * password or neither will be assigned a value
     *  
     * @param array $vars The know initialization variables
     * 
     * @return array The complete, environment aware state of initialization 
     *               variables
     */
    private function _getEnvironmentContext($vars = array())
    {
        $needed = array(
            'host',
            'user',
            'password',
            'hash'
        );
        $userInfo = array(
            'name' => '',
            'dir' => ''
        );
        foreach ($needed as $key) {
            if (!array_key_exists($key, $vars)) {
                $vars[$key] = '';
            }
        }
        if (empty($vars['host'])) {
            $vars['host'] = '127.0.0.1';
        }
        if (empty($vars['user'])) {
            //@codeCoverageIgnoreStart
            if (($info = posix_getpwuid(posix_geteuid()))) {
                $userInfo = $info;
            }
            //@codeCoverageIgnoreEnd
            $vars['user'] = $userInfo['name'];
        }
        if (empty($vars['hash']) && empty($vars['password'])) {
            $authArray = $this->_getEUIDAuth();
            $vars = array_merge($vars, $authArray);
        }
        return $vars;
    }
    /**
     * Fetch either hash or password for the effective user.
     * 
     * Hash will be sourced from .accesshash in the user's home directory
     * Password will be sourced from the script's environment (not $_ENV) if
     *  it is visable and the script was spawned from cpsrvd.
     *  
     * The returned array will have a 'hash' and 'password' key.  Either hash or
     * password or neither will have a value, but never both.
     * 
     * @return array An array containing authentication information
     */
    private function _getEUIDAuth()
    {
        $euid = posix_geteuid();
        //@codeCoverageIgnoreStart
        if (!($userInfo = posix_getpwuid($euid))) {
            return array();
        }
        //@codeCoverageIgnoreEnd
        $authInfo = array(
            'hash' => '',
            'password' => ''
        );
        $hashfile = $userInfo['dir'] . '/.accesshash';
        // fetch access hash only if it exists and is owned by the
        //  effective user
        if (file_exists($hashfile)) {
            $file = stat($hashfile);
            if ($file['uid'] === $euid) {
                $authInfo['hash'] = file_get_contents($hashfile);
            }
        }
        // fetch password only if hash was not found and we're running as the
        //  user under a cpsrvd spawned process
        if (empty($authInfo['hash'])) {
            $remotePassword = getenv('REMOTE_PASSWORD');
            $server = getenv('SERVER_SOFTWARE');
            if (!empty($remotePassword)
                && $remotePassword != '__HIDDEN__'
                && strpos($server, 'cpsrvd') === 0
            ) {
                $authInfo['password'] = $remotePassword;
            }
        }
        return $authInfo;
    }
    /**
     * Simple check to see if the script is a LivePHP script, spawned by cpsrvd
     * 
     * @return bool
     */
    public function isLocalQuery()
    {
        $socketfile = getenv('CPANEL_PHPCONNECT_SOCKET');
        if (file_exists($socketfile)) {
            return true;
        }
        return false;
    }
    /**
     * For use by extending classes to determine if an adapter type is valid for
     * the Service object instance, and if so, to normalize the name.
     * 
     * Implementing classes can use this to force the use of a particular 
     * adapter as necessary. For example, if the script is being executed for
     * a local query, it might be advantage to force the use of a
     * Cpanel_Abstract_LocalQuery based adapter, despite a request for a 
     * Cpanel_Abstract_RemoteQuery.
     * 
     * Implementing classes should return a string of the normalized name to 
     * be used for adapter spawning and demarcation.  If the $type is invalid
     * the method should return false.
     * 
     * @param string $type Adapter name to validate and, potentially, normalize
     * 
     * @return string|bool normalized name or false if invalid for Service
     */
    abstract protected function validAdapter($type);
    /**
     * Stores the adapter name with a Cpanel_Query_Object
     * 
     * If $adapterName is not passed, {@link getDefaultAdapterName()} will 
     * populate it.  If passed, {@link validAdapter()} will be called with that
     * as is input argument.
     * 
     * NOTE: if {@link isLocalQuery} returns true and a Live adapter has not 
     * been computed as the adapter type and the Live adapter is not disabled,
     * the method will "optimize" by setting the adapter to a Live type and mark
     * such in the Cpanel_Query_Objects.
     * 
     * @param Cpanel_Query_Object $rObj        Response object to update
     * @param string              $adapterName Adapter name to pin to $rObj
     * 
     * @todo Consider implementing this only as the concrete service class level
     *       or make more robust concerning optimization
     *         
     * @return Cpanel_Query_Object
     */
    protected function updateResponseObjectAdapter(Cpanel_Query_Object $rObj, $adapterName = '')
    {
        if ($adapterName) {
            $normalized = $this->validAdapter($adapterName);
            $adapterName = ($normalized) ? $normalized : $this->getDefaultAdapterName();
        } else {
            $adapterName = $this->getDefaultAdapterName();
        }
        if (($this->isLocalQuery())
            && ($adapterName !== self::ADAPTER_LIVE)
            && (!in_array(self::ADAPTER_LIVE, $this->disabledAdapters))
        ) {
            $rObj->query->optimized = true;
            $rObj->query->adapter = self::ADAPTER_LIVE;
        } else {
            $rObj->query->adapter = $adapterName;
        }
        return $rObj;
    }
    /**
     * Generate a Cpanel_Query_Object for the Service object instance
     * 
     * @param string $adapterName Optional adapterName to pin to the generated
     *                                          resposne object
     * 
     * @return Cpanel_Query_Object
     */
    public function genResponseObject($adapterName = '')
    {
        $rObj = new Cpanel_Query_Object();
        return $this->updateResponseObjectAdapter($rObj, $adapterName);
    }
    /**
     * Retrieve or spawn an adapter from a given response object
     * 
     * This is the primary method for retrieve the adapter for a query call.  It
     * first looks as the response object to see if an adapter name has been
     * previously determine; if necessary pinning one 
     * {@link updateResponseObjectAdpater()}.  Second it looks for the named 
     * adapter in storage; if not found it spawns one {@link spawnAdapter()} and
     * stores it. Lastly the adapter is returned
     * 
     * @param Cpanel_Query_Object $rObj Response object
     * 
     * @return mixed               An adapter for making queries
     * @throws Exception If pinned adapter in response object has been disabled
     */
    public function getAdapter(Cpanel_Query_Object $rObj)
    {
        $adapterType = $rObj->query->adapter;
        if (empty($adapterType)) {
            $this->updateResponseObjectAdapter($rObj, $this->getDefaultAdapterName());
            $adapterType = $rObj->query->adapter;
        } elseif (in_array($adapterType, $this->disabledAdapters)) {
            throw new Exception("Requested adapter '{$adapterType}' has been disabled");
        }
        if (array_key_exists($adapterType, $this->adapters)) {
            $a = $this->adapters[$adapterType];
        } else {
            $a = $this->spawnAdapter($adapterType);
            $this->adapters[$adapterType] = $a;
        }
        return $a;
    }
    /**
     * For use by extending classes to generate an appropriate adapter object
     * based on name.
     * 
     * @param string $adapterType Name of adapter to spawn.
     * 
     * @see    Cpanel_Service_Abstract::getAdapter()
     * 
     * @return mixed  An adapter for making queries
     */
    abstract protected function spawnAdapter($adapterType);
    /**
     * Validate query arguments for given service adapter name
     * 
     * @param string $service A service adapter name to validate against
     * @param array  $mf      Array representing key parameters for the query call
     * @param array  $args    Array of arguments for the query call
     * @param string $method  Name of method invoking the method (for error msg)
     * @param string $argType Array type to valid $args against
     * 
     * @return bool      True if all validation passes
     * @throws Exception If $service is not defined
     * @throws Exception If $mf is not defined
     * @throws Exception If $service is an invalid service adapter name
     * @throws Exception If $service is Live and script is not local 
     *                   {@link isLocalQuery()}
     * @throws Exception If whostmgr and $mf doesn't define module, function and
     *                   user
     * @throws Exception If cpanel and $mf doesn't define module, function 
     * @throws Exception If $args array is not observed to be of the same
     *                   type of array indicated by $argType
     */
    protected function checkParams($service, $mf, $args, $method, $argType)
    {
        // Verify service and mf has something
        if (empty($service)) {
            throw new Exception("{$method} requires a service type");
        } elseif (empty($mf)) {
            throw new Exception("{$method} requires a module-function array");
        }
        $normalized = $this->validAdapter($service);
        if ($normalized === false) {
            throw new Exception("Invalid service adapter '" . $service . "'");
        } elseif ($normalized == self::ADAPTER_LIVE && !$this->isLocalQuery()) {
            // this doesn't catch when dev fails to disable live adapter
            throw new Exception(
                "Cannot communicate with cpaneld. Invalid service adapter '"
                . self::ADAPTER_LIVE . "'"
            );
        }
        // Verify mf has keys necessary for said service
        if ($normalized == self::ADAPTER_WHM) {
            $matchedKeys = array_intersect(
                array(
                    'module',
                    'function',
                    'user'
                ),
                array_keys($mf)
            );
            if (count($matchedKeys) != 3) {
                throw new Exception(
                    "{$method} requires both 'module','function', and 'user' "
                    . "be defined in module-function array"
                );
            }
        } else {
            $matchedKeys = array_intersect(
                array(
                    'module',
                    'function'
                ),
                array_keys($mf)
            );
            if (count($matchedKeys) != 2) {
                throw new Exception(
                    "{$method} requires both 'module' and 'function' "
                    . "be defined in module-function array"
                );
            }
        }
        // If arguments are to be pass, verify their are stored properly for service
        if (!empty($args)) {
            if (!is_array($args)) {
                throw new Exception("Arguments to {$method} must be an array");
            } elseif ($this->arrayType($args) != $argType) {
                throw new Exception(
                    "Arguments to {$method} must be an {$argType} array"
                );
            }
        }
        return true;
    }
    /**
     * Utility method for determining the array type (oridinal or asssociative)
     * of a given array
     * 
     * @param array $arr Array to analysis
     * 
     * @return string    Determine array type for given array
     * @throws Exception If $arr is not an array or is empty
     */
    protected function arrayType($arr)
    {
        if (!is_array($arr) || empty($arr)) {
            throw new Exception(
                __FUNCTION__
                . ' expects an array with one or more elements'
            );
        }
        ksort($arr);
        $arr = array_keys($arr);
        $fkey = array_shift($arr);
        if (is_int($fkey)) {
            return self::API1ARGS;
        }
        return self::API2ARGS;
    }
    /**
      * Legacy support for scripts that set the desired PHP structure for a given
      * query via a "set_output" method.
      * 
      * This will only set the response format type for the generic adapter.
      * More sophisticated Service objects should have their calling scripts use
      * a more appropriate set of methods (available in the Service and Response
      * objects) and not this legacy support method.
      * 
     * NOTE: This may be deprecated in future releases
     * 
     * @param string $type Response format type desired from server.
     * 
     * @return Cpanel_Service_Abstract
     */
    public function set_output($type)
    {
        $adapterName = $this->getDefaultAdapterName();
        if (array_key_exists($adapterName, $this->adapters)) {
            $a = $this->adapters[$adapterName];
        } else {
            $rObj = $this->genResponseObject($adapterName);
            $a = $this->getAdapter($rObj);
        }
        $a->setAdapterResponseFormatType($type);
        return $this;
    }
    /**
     * Proxy accessor method for pushing user into adapters
     * 
     * @param string $user Value to set
     * 
     * @return Cpanel_Service_Abstract
     */
    public function setUser($user)
    {
        $this->setOptions(array('user'=>$user));
        foreach ($this->adapters as $a) {
            if (method_exists($a, 'setUser')) {
                $a->setUser($user);
            }
        }
        return $this;
    }
    /**
     * Proxy accessor method for pushing password into adapters
     * 
     * @param string $password Value to set
     * 
     * @return Cpanel_Service_Abstract
     */
    public function setPassword($password)
    {
        $this->setOptions(array('password'=>$password));
        foreach ($this->adapters as $a) {
            if (method_exists($a, 'setPassword')) {
                $a->setPassword($password);
            }
        }
        return $this;
    }
    /**
     * Proxy accessor method for pushing token into adapters
     * 
     * @param string $token Value to set
     * 
     * @return Cpanel_Service_Abstract
     */
    public function setToken($token)
    {
        $this->setOptions(array('token'=>$token));
        foreach ($this->adapters as $a) {
            if (method_exists($a, 'setToken')) {
                $a->setToken($token);
            }
        }
        return $this;
    }
    /**
     * Proxy accessor method for pushing host into adapters
     * 
     * @param string $host Value to set
     * 
     * @return Cpanel_Service_Abstract
     */
    public function setHost($host)
    {
    	$this->setOptions(array('host'=>$host));
        foreach ($this->adapters as $a) {
            if (method_exists($a, 'setHost')) {
                $a->setHost($host);
            }
        }
        return $this;
    }
    /**
     * Proxy accessor method for pushing hash into adapters
     * 
     * @param string $hash Value to set
     * 
     * @return Cpanel_Service_Abstract
     */
    public function setHash($hash)
    {
        $this->setOptions(array('hash'=>$hash));
        foreach ($this->adapters as $a) {
            if (method_exists($a, 'setHash')) {
                $a->setHash($hash);
            }
        }
        return $this;
    }
}
?>