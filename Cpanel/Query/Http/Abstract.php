<?php
/**
 * Cpanel_Query_Http_Abstract
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
 * @category   Cpanel
 * @package    Cpanel_Query
 * @subpackage Http
 * @author     David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright  (c) 2011 cPanel, Inc., All rights Reserved. (http://cpanel.net) 
 * @license    http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version    0.1.0
 * @link       http://sdk.cpanel.net
 * @since      0.1.0
 */
/**
 * Abstract class for remote queries
 *
 * @class      Cpanel_Query_Http_Abstract
 * @category   Cpanel
 * @package    Cpanel_Query
 * @subpackage Http
 * @author     David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright  (c) 2011 cPanel, Inc., All rights Reserved. (http://cpanel.net) 
 * @license    http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version    0.1.0
 * @link       http://sdk.cpanel.net
 * @since      0.1.0
 */
abstract class Cpanel_Query_Http_Abstract extends Cpanel_Core_Object
    implements Cpanel_Query_Http_Interface
{
    /**
     * Host address
     */
    protected $host = '127.0.0.1';
    /**
     * HTTP communication port
     */
    protected $port = '2087';
    /**
     * HTTP communication protocol
     */
    protected $protocol = 'https';
    /**
     * cPanel authentication type
     * 'pass'|'hash'
     */
    protected $auth_type = null;
    /**
     * Authentication string
     */
    protected $auth = null;
    /**
     * Authenticating user
     */
    protected $user = null;
    /**
     * PHP remote query function set
     * 'curl'|'fopen'
     */
    protected $http_client = 'curl';
    /**
     * Internal tracker for adapter authentication initialization
     */
    protected $initialized = false;
    /**
     * Cosntructor
     * 
     * @param Cpanel_Query_Object $rObj Response object
     * 
     * @return Cpanel_Query_Http_Abstract
     * @throws Exception If $rObj is an invalid query/response object
     */
    public function __construct($rObj = '')
    {
        parent::__construct();
        if ($rObj) {
            if (!($rObj instanceof Cpanel_Query_Object)) {
                throw new Exception('Invalid QueryObject');
            }
            $this->setResponseObject($rObj);
        }
        return $this;
    }
    /**
     * Store a response object
     * 
     * @param Cpanel_Query_Object $rObj Response object to set
     * 
     * @return Cpanel_Query_Live_Abstract
     * @throws Exception If $rObj is an invalid query/response object
     */
    public function setResponseObject($rObj)
    {
        if (!($rObj instanceof Cpanel_Query_Object)) {
            throw new Exception('Attempting to store an invalid query object');
        }
        $this->setOptions(
            array(
                'responseObject' => $rObj
            )
        );
        return $this;
    }
    /**
     * Return the attached response object
     * 
     * @return Cpanel_Query_Object
     */
    public function getResponseObject()
    {
        return $this->responseObject;
    }
    /**
     * Initialize key parameters for the query.
     * 
     * All parameters to this function are optional and can be set via the
     * accessor functions
     * 
     * This will set a password based authentication.  See {@link setHash} if a
     * hash authentication is desired.
     * 
     * @param string $host         Host address to query
     * @param string $user         User to authentication with
     * @param string $password     Password to authenticate with
     * @param bool   $overridePrev If set to true, any previous variables will
     *  be re-evaluated
     *  
     * @return Cpanel_Query_Http_Abstract
     * @throws Exception If it is determined that a remote query cannot be made
     *  with either fopen or curl functions
     */
    public function init($host = null, $user = null, $password = null, $overridePrev = false)
    {
        if ($this->initialized && !$overridePrev) {
            return $this;
        }
        if ($user != null) {
            $this->user = $user;
        }
        // set via method!
        if ($password != null) {
            $this->setPassword($password);
        }
        if ($host == null) {
            THROW new Exception("No host defined");
        } else {
            $this->host = $host;
        }
        $client = $this->getValidClientType();
        if (is_null($client)) {
            throw new Exception(
                'Neither allow_url_fopen and curl are '
                . 'available in this PHP configuration'
            );
        }
        $this->http_client = $client;
        $this->setProtocol();
        $this->initialized = true;
        return $this;
    }
    /**
     * All extending class must be able to return the response format type (RFT)
     * as it will be passed to the response object (to determine the proper
     * method/parser to objectify the response)
     * 
     * @return string
     */
    abstract protected function getAdapterResponseFormatType();
    /**
     * All extending class must be able to set the response format type (RFT)
     * since the server interface may have multiple output formats, defined as
     * query time
     * 
     * @param string $type The response format type to set at query time and use
     *  for initial response parsing
     * 
     * @return Cpanel_Query_Http_Abstract
     * @throws Exception If an invalid RFT
     */
    abstract protected function setAdapterResponseFormatType($type);
    /**
     * Detemine whether to use cURL lib functions for fopen functions for query
     * 
     * @return string|void "curl" if cURL lib is available, "fopen" if cURL is
     *  not available and php.ini has allow_url_fopen set true, otherwise NULL
     */
    public function getValidClientType()
    {
        // @codeCoverageIgnoreStart
        if (function_exists('curl_setopt')) {
            $r = "curl";
        } elseif (ini_get('allow_url_fopen')) {
            $r = "fopen";
        } else {
            $r = null;
        }
        // @codeCoverageIgnoreEnd
        return $r;
    }
    /**
     * Accessor for setting host address
     * 
     * @param string $host Host address
     * 
     * @return Cpanel_Query_Http_Abstract
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;    
    }
    /**
     * Accessor for setting HTTP port
     * 
     * This method will also set the HTTP protocol based on a valid port
     * 
     * @param int $port Integer value of the HTTP port to query against
     * 
     * @return Cpanel_Query_Http_Abstract
     * @throws Exception If invalid port range
     * @throws Exception If a matching HTTP protocol cannot be found. i.e., not
     *  a valid port for communicating with cPanel products
     */
    public function setPort($port)
    {
        if (!is_int($port)) {
            $port = intval($port);
        }
        if ($port < 1 || $port > 65535) {
            throw new Exception(
                'non integer or negative integer passed to set_port'
            );
        }
        $proto = $this->getProtocolForPort($port);
        if (!$proto) {
            throw new Exception(
                "No protocol for port \"{$port}\" found."
            );
        }
        $this->setProtocol($proto);
        $this->port = $port;
        return $this;
    }
    /**
     * Determine the correct HTTP protocol for a port
     * 
     * @param string $port Port to evaluate
     * 
     * @return string|bool 'http' or 'https' for a valid port, otherwise FALSE
     */
    protected function getProtocolForPort($port)
    {
        $port = (string)$port;
        $ports = array(
            '2082' => 'http',
            '2086' => 'http',
            '2095' => 'http',
            '80' => 'http',
            '2087' => 'https',
            '2083' => 'https',
            '2096' => 'https',
        );
        $proto = (array_key_exists($port, $ports)) ? $ports[$port] : false;
        return $proto;
    }
    /**
     * Accessor to set the HTTP protocol
     *
     * @param string $proto The protocol to use for HTTP query: 'http'|'https'
     * 
     * @return Cpanel_Query_Http_Abstract
     * @throws Exception If value is not a registered PHP stream wrapper
     * @throws Exception If protocol is invalid. Cpanel_Query_Http_Abstract
     *                                    only accepts 'http' or 'https' 
     */
    public function setProtocol($proto = '')
    {
        if (empty($proto)) {
            // @codeCoverageIgnoreStart
            $protos = stream_get_wrappers();
            if (in_array('https', $protos)) {
                $proto = 'https';
            } elseif (in_array('http', $protos)) {
                $proto = 'http';
            } else {
                throw new Exception(
                    'No valid protocol stream wrapper registered'
                );
            }
            // @codeCoverageIgnoreEnd
            
        }
        if ($proto != 'https' && $proto != 'http') {
            throw new Exception(
                'Invalid protocol type: must be "http" or "https".'
            );
        }
        $this->protocol = $proto;
        return $this;
    }
    /**
     * Accessor to get HTTP protocol for current query
     * 
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }
    /**
     * Accessor to set the auth type
     *
     * Cpanel_Query_Http_Abstract can authenticate with either a remote access
     * hash and password.  This method will toggle which type should be used.
     *
     * @param string $auth_type 'pass'|'hash' The authentication type to use for
     *  query
     * 
     * @see    setPassword()
     * @see    setHash()
     * 
     * @return Cpanel_Query_Http_Abstract
     * @throws Exception If $auth_type is invalid
     */
    public function setAuthType($auth_type)
    {
        if ($auth_type != 'hash' && $auth_type != 'pass') {
            throw new Exception(
                'The only two allowable auth types are "hash" and "pass"'
            );
        }
        $this->auth_type = $auth_type;
        return $this;
    }
    /**
     * Accessor to set the authentication password
     *
     * The method will invoke {@link setAuthType} with 'pass'
     *
     * @param string $pass The password to authenticate with
     * 
     * @see    setAuthType()
     * 
     * @return Cpanel_Query_Http_Abstract
     */
    public function setPassword($pass)
    {
        $this->setAuthType('pass');
        $this->auth = $pass;
        return $this;
    }
    /**
     * Accessor to set the authentication hash
     *
     * The method will invoke {@link setAuthType} with 'hash'
     *
     * @param string $hash The hash value to authenticate with
     * 
     * @see    setAuthType()
     * 
     * @return Cpanel_Query_Http_Abstract
     */
    public function setHash($hash)
    {
        $this->setAuthType('hash');
        $this->auth = str_replace(
            array(
                "\n",
                "\r",
                "\s",
            ),
            '',
            $hash
        );
        return $this;
    }
    /**
     * Accessor to set authentication user
     * 
     * @param string $user User to authenticate query with
     * 
     * @return Cpanel_Query_Http_Abstract
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }
    /**
     * Convenience method for defining a hash based authentication
     * 
     * @param string $user User to authenticate query with
     * @param string $hash Hash value to authenticate with
     * 
     * @see    setHash()
     * @see    setUser()
     * 
     * @return Cpanel_Query_Http_Abstract
     */
    public function hashAuth($user, $hash)
    {
        return $this->setHash($hash)->setUser($user);
    }
    /**
     * Legacy method for defining a hash based authentication
     * 
     * @param string $user User to authenticate query with
     * @param string $hash Hash value to authenticate with
     * 
     * @see    hashAuth()
     * 
     * @return Cpanel_Query_Http_Abstract
     */
    public function hash_auth($user, $hash)
    {
        return $this->hashAuth($user, $hash);
    }
    /**
     * Convenience method for defining a password based authentication
     * 
     * @param string $user User to authenticate query with
     * @param string $pass password value to authenticate with
     * 
     * @see    setPassword()
     * @see    setUser()
     * 
     * @return Cpanel_Query_Http_Abstract
     */
    public function passwordAuth($user, $pass)
    {
        return $this->setPassword($pass)->setUser($user);
    }
    /**
     * Legacy method for defining a password based authentication
     * 
     * @param string $user User to authenticate query with
     * @param string $pass password value to authenticate with
     * 
     * @see    passwordAuth()
     * 
     * @return Cpanel_Query_Http_Abstract
     */
    public function password_auth($user, $pass)
    {
        return $this->passwordAuth($user, $pass);
    }
    /**
     * Accessor to set PHP HTTP query function set
     * 
     * @param string $client 'curl'|'fopen' PHP function set to use for query
     * 
     * @return Cpanel_Query_Http_Abstract
     * @throws Exception If invalid function set
     */
    public function setHttpClient($client)
    {
        if (($client != 'curl') && ($client != 'fopen')) {
            throw new Exception('only curl and fopen and allowed http clients');
        }
        $this->http_client = $client;
        return $this;
    }
    
    /**
     * Accessor to set remote host address
     * 
     * @param string $host Host address
     * 
     * @return Cpanel_Query_Http_Abstract
     */
//    public function setHost($host)
//    {
//        if (empty($host)) {
//            throw new Exception('Empty argument passed to ' . __FUNCTION__);
//        }
//        $this->host = $host;
//        return $this;
//    }
    /**
     * Determine if $str is, at minimum, a partial URL
     * 
     * @param string $str String to evaluate
     * 
     * @return bool  
     */
    public function isURL($str)
    {
        $str = str_replace(
            array(
                'http://',
                'https://',
            ),
            '',
            $str
        );
        return (strpos($str, '/') !== false) ? true : false;
    }
    /**
     * Determines if $data is a URL parameter string, i.e. key=value&key2=value2
     * 
     * If $strict is set to TRUE, the string will be evaluated for at least one
     * valid key/value pair.  The default is FALSE.  This method is used 
     * primarily with regard to the 'direct URL' feature, since a user can pass
     * an array or string of key/value pairs for use in an http query (as
     * defined by the PublicAPI interface)
     * 
     * @param mixed $data   String or Array to evaluate. Arrays will always eval
     *  to FALSE.
     * @param bool  $strict If $data is a String & this parameter is TRUE,
     *  additional logic is applied to determine if $data is similar to an http
     *  query string
     * 
     * @return bool 
     */
    public function isURLParamStr($data, $strict = false)
    {
        $r = (is_string($data)) ? true : false;
        if ($strict && $r) {
            $r = (bool)strpos($data, '=');
        }
        return $r;
    }
    /**
     * Build a query URL from the provided $function and internal properties
     * 
     * The method only has meaning in process immediately preceding a remote
     * query call since a variety of internal properties must exist for a
     * meaningful url to be constructed
     * 
     * @param string $function Remote function to call
     * @param bool   $isURL    if $function is a partial URL {@link isURL()}
     * 
     * @return string    Constructed URL string, including protocol, host, port and
     *                   function; no arguments to the function are present
     * @throws Exception If required internal property is not set 
     */
    public function buildURL($function, $isURL = false)
    {
        $rObj = $this->getResponseObject();
        if (!($rObj instanceof Cpanel_Query_Object)) {
            throw new Exception('Invalid QueryObject');
        }
        $vars['function'] = $function;
        $vars['host'] = $this->host;
        $vars['port'] = $this->port;
        $vars['queryFormatType'] = strtolower($rObj->getResponseFormatType());
        foreach ($vars as $key => $value) {
            if (empty($value)) {
                throw new Exception("URL not possible: $key not set.");
            }
        }
        if ($isURL) {
            $vars['queryPath'] = (strpos($vars['function'], '/') !== 0) ? '/' : '';
        } else {
            $vars['queryPath'] = "/{$vars['queryFormatType']}-api/";
        }
        $vars['proto'] = $this->getProtocol();
        if (empty($vars['proto'])) {
            $vars['proto'] = $this->setProtocol();
        }
        $url = $vars['proto'] 
             . '://'
             . $vars['host']
             . ':'
             . $vars['port']
             . $vars['queryPath']
             . $vars['function'];
        return $url;
    }
    /**
     * Return an HTTP authentication header string
     * 
     * @return string    Authentication header string based on internal properties
     * @throws Exception If invalid protected property "auth_type" 
     */
    public function buildAuthStr()
    {
        if ($this->auth_type == 'hash') {
            return 'Authorization: WHM '
                  . $this->user
                  . ':'
                  . $this->auth
                  . "\r\n";
        } elseif ($this->auth_type == 'pass') {
            return 'Authorization: Basic '
                 . base64_encode($this->user . ':' . $this->auth)
                 . "\r\n";
        } else {
            THROW new Exception('invalid auth_type set');
        }
    }
    /**
     * Execute an HTTP query based on a qurey/response object
     * 
     * After executing the underlying PHP query function, the server response
     * is parsed by the response object.
     * 
     * This method should not be invoked directly, but instead through
     * {@link makeQuery()}
     * 
     * @param Cpanel_Query_Object $rObj Query object responsible for parsing
     *  server response
     * 
     * @see    makeQuery()
     * 
     * @return Cpanel_Query_Object
     * @throws Exception If invalid $rObj
     * @throws Exception If $rObj specifies a PHP query function that does not
     *  have a corresponding proxy method in Cpanel_Query_Http_Abstract
     */
    public function exec($rObj)
    {
        if (!($rObj instanceof Cpanel_Query_Object)) {
            throw new Exception('Invalid QueryObject');
        }
        $client = $rObj->query->client;
        $func = "{$client}Query";
        if (empty($client) || !method_exists($this, $func)) {
            THROW new Exception("Query client $client is not defined");
        }
        return $rObj->parse($this->$func($rObj));
    }
    /**
     * Method for invoking a remote query
     * 
     * This method should be used by all extending classes, interfaces and
     * Services
     * 
     * This method will set the following in the response objects query space:
     *   client        string - 'curl'|'fopen' PHP function set to query with 
     *   url        string - built query string {@link buildURL()}
     *   args        string - HTTP URL query parameter string
     *   argsArray    array  - Array of query parameters
     *   authstr    string - HTTP header authentication string
     *   directURL  bool   - If $function was determined to be a partial URL
     * 
     * @param string $function An API function name or partial URL
     * @param array  $vars     URL arguments relative to requested $function
     * 
     * @return Cpanel_Query_Object
     * @throws Exception If empty $function
     * @throws Exception If internal property "user" is not defined
     * @throws Exception If internal properties for authentication are not
     *                             defined
     * @throws Exception If a response object was not previous attached
     */
    public function makeQuery($function, $vars = array())
    {
        // Check to make sure all data needed to perform the query is in place
        if (!$function) {
            THROW new Exception('Invalid function/URL argument');
        }
        if ($this->user === null || $this->user === false) {
            THROW new Exception('No user has been set');
        }
        if ($this->auth === null || $this->auth === false) {
            THROW new Exception('No authentication information has been set');
        }
        $vars = (!is_array($vars)) ? array() : $vars;
        $isURL = $this->isURL($function);
        if ($isURL) {
            $url = $this->buildURL($function, true);
            if ($this->isURLParamStr($vars)) {
                $args = $vars;
            } else {
                // @codeCoverageIgnoreStart
                $args = http_build_query($vars, '', '&');
                // @codeCoverageIgnoreEnd
                
            }
        } else {
            $url = $this->buildURL($function);
            // @codeCoverageIgnoreStart
            $args = http_build_query($vars, '', '&');
            // @codeCoverageIgnoreEnd
            
        }
        // Set the $auth string
        $authstr = $this->buildAuthStr();
        // Perform the query
        $rArgs = array();
        if ($this->listner) {
            $rArgs['listner'] = $this->listner;
        }
        $rObj = $this->getResponseObject();
        if (!($rObj instanceof Cpanel_Query_Object)) {
            throw new Exception('Query object not set prior to query execution');
        }
        $rObj->setOptions($rArgs);
        $rObj->query->client = $this->http_client;
        $rObj->query->url = $url;
        $rObj->query->args = $args;
        $rObj->query->argsArray = $vars;
        $rObj->query->authstr = $authstr;
        $rObj->query->directURL = $isURL;
        return $this->exec($rObj);
    }
    /**
     * Proxy method for PHP's curl function set
     * 
     * The method should not be called directly, instead invoke 
     * {@link makeQuery()} after calling {@link setHttpClient()} with "curl"
     * 
     * @param Cpanel_Query_Object $rObj Response object to source and update
     *  data as necessary for the current query
     * 
     * @return string Raw server response string
     */
    public function curlQuery($rObj)
    {
        if (!($rObj instanceof Cpanel_Query_Object)) {
            throw new Exception('curlQuery requires a QueryObject argument');
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        // Return contents of transfer on curl_exec
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // Allow self-signed certs
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        // Increase buffer size to avoid "funny output" exception
        curl_setopt($curl, CURLOPT_BUFFERSIZE, 131072);
        // Pass authentication header
        $curlHeader = $this->buildCurlHeaders($curl, $rObj);
        $url = $rObj->query->url;
        // Set the URL
        curl_setopt($curl, CURLOPT_URL, $url);
        // Set Headers
        curl_setopt($curl, CURLOPT_HTTPHEADER, $curlHeader);
        if ($this->listner) {
            $this->listner->log(
                'debug',
                "cURL Query:\n\tURL: {$url}\n\tDATA: {$rObj->query->args}\n"
                . "\tAUTH: {$rObj->query->authstr}\n"
            );
        }
        $result = $this->curlExec($curl);
        curl_close($curl);
        return $result;
    }
    /**
     * Wrapper for PHP's curl_exec function
     * 
     * This method should not be called directly, instead invoke 
     * {@link makeQuery()} after calling {@link setHttpClient()} with "curl"
     * 
     * @param resource $curl cURL resource to invoke curl_exec on
     * 
     * @return string    Raw server response string
     * @throws Exception If $curl is not a resource
     * @throws Exception If curl_exec fails
     */
    protected function curlExec($curl)
    {
        if (!is_resource($curl)) {
            throw new Exception('Invalid cURL resource');
        }
        $result = curl_exec($curl);
        if ($result === false) {
            $msg = 'curl_exec threw error "' . curl_error($curl) . '"';
            $rObj = $this->getResponseObject();
            if ($rObj instanceof Cpanel_Query_Object) {
                $url = $rObj->query->url;
                $postdata = $rObj->query->args;
                $msg.= ' for "' . $url . '" and data "' . $postdata;
            }
            throw new Exception($msg);
        }
        // @codeCoverageIgnoreStart
        return $result;
        // @codeCoverageIgnoreEnd
        
    }
    /**
     * Update cURL resource POST field data and return a header string
     * 
     * @param resource $curl      cURL resource to update
     * @param string   $headerStr Previous built header string
     * @param string   $postdata  URL query parameter string to append to header
     *  string
     * 
     * @return string    Appended header string
     * @throws Exception If $curl is not a resource
     */
    protected function addCurlPostFields($curl, $headerStr, $postdata)
    {
        if (!is_resource($curl)) {
            throw new Exception('Invalid cURL resource');
        }
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
        return $headerStr . "\r\n" . $postdata;;
    }
    /**
     * Build a cURL header array, updating cURL POST field data as necessasry
     * 
     * @param resource            $curl cURL resource
     * @param Cpanel_Query_Object $rObj Response object to source data
     *                                   from
     * 
     * @return array               Array of cURL headers to set
     * @throws Exception If $rObj is an invalid object 
     */
    protected function buildCurlHeaders($curl, $rObj)
    {
        if (!($rObj instanceof Cpanel_Query_Object)) {
            throw new Exception(
                __FUNCTION__ . ' requires a QueryObject argument'
            );
        }
        $queryObj = $rObj->getQuery();
        $postdata = $queryObj->args;
        $authstr = $queryObj->authstr;
        // Make general headers
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
        );
        // Merge any custom headers
        $h = $queryObj->httpHeaders;
        if ($h) {
            $customHeaders = $h->getAllDataRecursively();
            $headers = $customHeaders + $headers;
        }
        foreach ($headers as $key => $value) {
            $headerStrs[] = "{$key}: {$value}";
        }
        $curlHeader[0] = $authstr . implode("\r\n", $headerStrs);
        $qt = $queryObj->httpQueryType;
        if ($qt && strtoupper($qt) == 'GET') {
            $queryObj->url = "{$queryObj->url}?{$postdata}";
        } else {
            $curlHeader[0] = $this->addCurlPostFields(
                $curl,
                $curlHeader[0],
                $postdata
            );
        }
        return $curlHeader;
    }
    /**
     * Proxy method for PHP's fopen function set
     * 
     * The method should not be called directly, instead invoke 
     * {@link makeQuery()} after calling {@link setHttpClient()} with "fopen"
     * 
     * @param Cpanel_Query_Object $rObj Response object to source and update
     *  data as necessary for the current query
     * 
     * @return string Raw server response string
     * @throws Exception If $rObj is an invalid object
     * @throws Exception If PHP's "allow_url_fopen" ini setting is FALSE
     * @throws Exception If protocol is not a valid, registered stream type 
     * @throws Exception If it's determined that internal properties port and
     *  protocol are not properly paired
     */
    public function fopenQuery($rObj)
    {
        if (!($rObj instanceof Cpanel_Query_Object)) {
            throw new Exception(
                __FUNCTION__ . ' requires a QueryObject argument'
            );
            //@codeCoverageIgnoreStart
            
        } elseif (!(ini_get('allow_url_fopen'))) {
            throw new Exception(
                'fopen_query called on system without '
                . 'allow_url_fopen enabled in php.ini'
            );
            //@codeCoverageIgnoreEnd
            
        } else {
            $queryObj = $rObj->getQuery();
            $url = $queryObj->url;
            $proto = $this->getProtocol();
        }
        //@codeCoverageIgnoreStart
        if (!in_array($proto, stream_get_wrappers())) {
            throw new Exception("No valid stream wrapper for $proto");
        }
        //@codeCoverageIgnoreEnd
        if (strpos($url, $proto . ":") !== 0) {
            $msg = 'Procotol and URL mis-match. '
                 . "Protocol: {$proto} "
                 . "URL: {$url}";
            throw new Exception($msg);
        }
        $opts = $this->buildFopenContextOpts($rObj);
        $context = stream_context_create($opts);
        // Recheck, buildFopenContextOpts may have altered URL for GET requests
        $url = $queryObj->url;
        $result = $this->fopenExec($url, false, $context);
        return $result;
    }
    /**
     * Build fopen's context option array
     * 
     * @param Cpanel_Query_Object $rObj Response object to source and
     *  update data as necessary
     * 
     * @return array Array of elements appropriate for use with PHP's
     *  "stream_context_create()" function
     * @throws Exception If $rObj is an invalid object
     */
    protected function buildFopenContextOpts($rObj)
    {
        if (!($rObj instanceof Cpanel_Query_Object)) {
            throw new Exception(
                __FUNCTION__ . ' requires a QueryObject argument'
            );
        }
        $queryObj = $rObj->getQuery();
        $postdata = $queryObj->args;
        $authstr = $queryObj->authstr;
        $opts = array(
            'http' => array(
                'allow_self_signed' => true,
                'method' => 'POST',
                'header' => '',
            )
        );
        // Make general headers
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Content-Length' => strlen($postdata),
        );
        // Merge any custom headers
        $h = $queryObj->httpHeaders;
        if ($h) {
            $customHeaders = $h->getAllDataRecursively();
            $headers = $customHeaders + $headers;
        }
        foreach ($headers as $key => $value) {
            $headerStrs[] = "{$key}: {$value}";
        }
        $fopenHeaderStr = $authstr . implode("\r\n", $headerStrs);
        $qt = $queryObj->httpQueryType;
        if ($qt && strtoupper($qt) == 'GET') {
            $queryObj->url = "{$queryObj->url}?{$postdata}";
            $opts['http']['method'] = $qt;
        } else {
            $fopenHeaderStr.= "\r\n{$postdata}";
        }
        $opts['http']['header'] = $fopenHeaderStr;
        return $opts;
    }
    /**
     * Wrapper for PHP's file_get_contents() function
     * 
     * This method should not be called directly, instead invoke 
     * {@link makeQuery()} after calling {@link setHttpClient()} with "fopen"
     * 
     * @param string   $url              URL string to pass to file_get_contents()
     * @param bool     $use_include_path For possible future implementation.
     *  This implementation will always pass FALSE to file_get_contents()'s
     *  second argument.
     * @param resource $context          Stream context resource
     * 
     * @return string    Raw server response 
     * @throws Exception If $context is not a resource
     * @throws Exception If $url is not a URL
     */
    protected function fopenExec($url, $use_include_path, $context)
    {
        if (!is_resource($context)) {
            throw new Exception('Context stream not set');
        }
        if (!$this->isURL($url)) {
            throw new Exception('Invalid URL');
        }
        // @codeCoverageIgnoreStart
        return file_get_contents($url, false, $context);
        // @codeCoverageIgnoreEnd
        
    }
    /**
     * Magic accessor
     * 
     * "get_key" will fetch internal property $key
     * "getKey" will fetch internal property $key
     * "setKey($value)" will set internal property $key=$value 
     * "setKeyMore($value)" will set internal property $key_more=$value
     *
     * NOTE: Because Cpanel_Query_Http_Abstract extends 
     * Cpanel_Core_Object, an "internal property" may exist at runtime
     * with the private "_data" property or may exist as an explicit protected
     * property of this class
     * 
     * @param string $method Method name as invoked by calling script
     * @param array  $args   Method arguments passed in invoked method
     * 
     * @return mixed    
     * @throws Exception If not a valid accessor method
     */
    public function __call($method, $args)
    {
        //legacy accessor methods
        if (strpos($method, 'get_') === 0) {
            $key = substr($method, 4);
            if (property_exists($this, strtolower($key))) {
                $r = $this->$key;
            } else {
                $r = $this->getOption($key);
            }
        } elseif (strpos($method, 'set_') === 0) {
            $key = substr($method, 4);
            $value = (array_key_exists(0, $args)) ? $args[0] : null;
            $nameParts = explode('_', $key);
            if (count($nameParts) > 1) {
                array_walk(
                    $nameParts,
                    create_function('&$v', '$v = ucfirst(strtolower($v));')
                );
                $mname = 'set' . implode('', $nameParts);
            } else {
                $mname = 'set' . ucfirst(strtolower($key));
            }
            if (method_exists($this, $mname)) {
                $r = $this->$mname($value);
            } else {
                $r = $this->setOptions(array($key => $value));
            }
        } elseif (strpos($method, 'get') === 0) {
            $key = strtolower(substr($method, 3));
            if (property_exists($this, $key)) {
                $r = $this->$key;
            } else {
                $r = $this->getOption($key);
            }
        } elseif (strpos($method, 'set') === 0) {
            $key = strtolower(substr($method, 3));
            $value = (array_key_exists(0, $args)) ? $args[0] : null;
            if (property_exists($this, $key)) {
                $this->$key = $value;
                $r = $this;
            } else {
                $r = $this->setOption(array($key => $value));
            }
        } else {
            throw new Exception("Undefined method $method");
        }
        return $r;
    }
}
?>