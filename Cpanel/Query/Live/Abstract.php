<?php
/**
 * Cpanel_Query_Live_Abstract
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
 * @category   Cpanel
 * @package    Cpanel_Query
 * @subpackage Live
 * @author     David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright  (c) 2011 cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license    http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version    0.1.0
 * @link       http://sdk.cpanel.net
 * @since      0.1.0
 */
/**
 * Abstract class for queries made to the local system over a socket opened by
 * cpsrvd. 
 *
 * @class      Cpanel_Query_Live_Abstract
 * @category   Cpanel
 * @package    Cpanel_Query
 * @subpackage Live
 * @author     David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright  (c) 2011 cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license    http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version    0.1.0
 * @link       http://sdk.cpanel.net
 * @since      0.1.0
 */
abstract class Cpanel_Query_Live_Abstract extends Cpanel_Core_Object
    implements Cpanel_Query_Live_Interface
{
    /**
     * File handle resource of the cpsrvd socket
     */
    private $_cpanelfh;
    /**
     * Internal tracker for knowning if $_cpanelfh is a valid resource 
     */
    protected $connected = 0;
    /**
     * Name of socket file spawned by cpsrvd for the current running process
     */
    protected $socketfile;
    /**
     * Timeout used by {@link safeClose()}
     */
    const SOCKET_TIMEOUT = 10;
    /**
     * Constructor
     * 
     * This constructor will define a shutdown function.  This helps ensure the
     * cpsrvd receives the proper shutdown notification and the socket file is 
     * properly closed. See {@link closeCpanelHandle()}
     * 
     * @param Cpanel_Query_Object $rObj Optionally attach the response
     *  object at instantiation.
     * 
     * @return Cpanel_Query_Live_Abstract
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
        if (!$this->socketTimeout) {
            $this->socketTimeout = self::SOCKET_TIMEOUT;
        }
        register_shutdown_function(
            array(
                $this,
                'closeCpanelHandle'
            )
        );
        return $this;
    }
    /**
     * Store a response object
     * 
     * @param Cpanel_Query_Object $rObj Response object
     * 
     * @return Cpanel_Query_Live_Abstract
     * @throws Exception If $rObj is an invalid query/response object
     */
    public function setResponseObject($rObj)
    {
        if (!($rObj instanceof Cpanel_Query_Object)) {
            throw new Exception('Attempting to store an invalid query object');
        }
        $this->setOptions(array('responseObject' => $rObj));
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
     * Perform a query over the local, "live" socket
     * 
     * All client, Service and API interfaces should use this method. 
     * 
     * @param string $reqtype Cpanel action type: 'exec'|'feature'|'if'
     * @param string $version String value of the cPanel API call: '1'|'2'
     * @param string $module  cPanel API module name
     * @param string $func    cPanel API function name
     * @param array  $args    Arguments for the function call
     * 
     * @return Cpanel_Query_Object
     * @throws Exception If $args is anthing other than an array and $reqtype
     *  is 'exec' and module is not 'print'.  Only 'exec'->'print' can accept
     *  a string value
     */
    public function makeQuery($reqtype, $version, $module, $func, $args = array())
    {
        $query = array(
            "module" => $module,
            "reqtype" => $reqtype,
            "func" => $func,
            "apiversion" => (string)$version, //legacy has this as string?
            
        );
        if (!empty($args)) {
            if (!is_array($args) && ($reqtype == 'exec' && $module != 'print')) {
                throw new Exception(
                    __FUNCTION__ . ' only accepts an array for API arguments'
                );
            }
            $query['args'] = $args;
        }
        $rObj = $this->getResponseObject();
        $rObj->setQuery($query);
        $rObj->getResponseParser()->encodeQuery($rObj);
        return $this->exec($rObj);
    }
    /**
     * Create a file handle attached to a socket file, spawned by cpsrvd for the
     * current running process
     * 
     * @see    Cpanel_Query_Live_Interface::openCpanelHandle()
     * 
     * @return bool      value passed back from {@link _initJSONMode()}
     * @throws Exception If socket file cannot be located
     * @throws Exception If file handle could not be made for socket file
     * @throws Exception If stream blocking could not be set for file handle
     */
    public function openCpanelHandle()
    {
        if ($this->connected && is_resource($this->_cpanelfh)) {
            return true;
        }
        $this->connected = 1;
        $socketfile = getenv('CPANEL_PHPCONNECT_SOCKET');
        if (empty($socketfile) || !file_exists($socketfile)) {
            $this->connected = 0;
            throw new Exception('No connection to the cPanel engine exists');
        }
        $this->socketfile = $socketfile;
        $this->_cpanelfh = fsockopen("unix://" . $socketfile);
        //@codeCoverageIgnoreStart
        if (!$this->_cpanelfh) {
            $this->connected = 0;
            throw new Exception(
                'There was a problem connecting back to the cPanel engine. '
                .'Make sure your script ends with ".live.php" or ".livephp"'
            );
        }
        if (!(stream_set_blocking($this->_cpanelfh, 1))) {
            $this->connected = 0;
            throw new Exception(
                __FUNCTION__ . ' unable to set stream blocking'
            );
        }
        //@codeCoverageIgnoreEnd
        // Initialize JSON data mode
        return $this->_initJSONMode();
    }
    /**
     * Initialize JSON mode with cpsrvd
     * 
     * @return bool      status of a read back from cpsrvd
     * @throws Exception If socket file was prematurely closed
     */
    private function _initJSONMode()
    {
        if (!$this->connected) {
            throw new Exception(
                'The LivePHP Socket has closed, unable to continue.'
            );
        }
        $this->_write('<cpaneljson enable="1">');
        return (bool)$this->_read();
    }
    /**
     * Write to socket file handle
     * 
     * @param string $code Raw string to transmit to cpsrvd via socket file
     * 
     * @return book   status of writing to the file handle
     */
    private function _write($code)
    {
        return (bool)fwrite($this->_cpanelfh, strlen($code) . "\n" . $code);
    }
    /**
     * Read the socket file handle
     * 
     * @return string Raw response string from cpsrd
     */
    private function _read()
    {
        $buffer = '';
        $result = '';
        while ($buffer = fgets($this->_cpanelfh)) {
            $result = $result . $buffer;
            if (strpos($buffer, '</cpanelresult>') !== false) {
                break;
            }
        }
        return $result;
    }
    /**
     * Perform a local, 'live' query
     * 
     * This method is responsible for completing the query/response transaction
     * with cpsrd.  It's possible to use this method directly in a client,
     * application, or subcase, however {@link makeQuery()} is the prefered 
     * method for completing the transaction.
     * 
     * This method will attempt to create a file handle for the socket file, if
     * it was not previously created. This helps ensure legacy support as well
     * as some dynamic edge cases.
     * 
     * @param string $code       Raw string to send to cpsrvd
     * @param int    $skipReturn Force a read, after writing, on the socket file
     *  handle, but discard the response and do not return response object
     * 
     * @see    Cpanel_Query_Live_Interface::exec()
     * @see    Cpanel_Query_Live_Abstract::makeQuery()
     * 
     * @return Cpanel_Query_Object|void if $skipReturn is set to 1, null
     *                                  will be returned
     * @throws Exception If a response object has not been previously set 
     */
    public function exec($code, $skipReturn = 0)
    {
        if (!$this->connected) {
            $this->openCpanelHandle();
        }
        $codeStr = '';
        if ($code instanceof Cpanel_Query_Object) {
            $rObj = $code;
            $parser = $rObj->getResponseFormatType();
            $codeStr = $rObj->query->$parser;
        } elseif (is_string($code)) {
            $rObj = $this->getResponseObject();
            if (!($rObj instanceof Cpanel_Query_Object)) {
                throw new Exception(
                    'Response object must be set prior to calling '
                    . __FUNCTION__
                    . 'with String input.'
                );
            }
            $codeStr = $code;
        }
        if (empty($codeStr)) {
            throw new Exception(__FUNCTION__ . ': Invalid input');
        }
        $this->_write($codeStr);
        $response = $this->_read();
        if ($skipReturn) {
            return;
        }
        return $rObj->parse($response);
    }
    /**
     * Send shutdown signal to cpsrvd and gracefully close the socket file
     * handle
     * 
     * NOTE: This method is registered as a shutdown function by the constructor
     * This enforced a clean shutdown and cleanup of resources before the
     * deconstructor is called.  This is necessary due to PHP's internal
     * deconstruction/garbage collection.  Otherwise a race condition is likely
     * to occur against the state of the file handle.
     * 
     * @see    Cpanel_Query_Live_Interface::closeCpanelHandle()
     * 
     * @return void
     */
    public function closeCpanelHandle()
    {
        $this->connected = 0;
        if (is_resource($this->_cpanelfh) && file_exists($this->socketfile)) {
            $this->_write('<cpanelxml shutdown="1" />');
            $this->safeClose();
        }
    }
    /**
     * Gracefully close the socket file handle and nullify internal properties
     * 
     * @return void
     */
    public function safeClose()
    {
        if (is_resource($this->_cpanelfh)) {
            $timeout = ($this->socketTimeout) ? $this->socketTimeout : self::SOCKET_TIMEOUT;
            stream_set_timeout($this->_cpanelfh, $timeout);
            $meta = stream_get_meta_data($this->_cpanelfh);
            while (!$meta['timed_out'] && !feof($this->_cpanelfh)) {
                $buf = fgets($this->_cpanelfh);
                $meta = stream_get_meta_data($this->_cpanelfh);
            }
            fclose($this->_cpanelfh);
        }
        $this->_cpanelfh = null;
    }
    /**
     * Deconstructor
     * 
     * NOTE: This method will call {@link closeCpanelHandle()} if protected
     * property $connected is set.  However, due to PHP's deconstruction this
     * should never be true.  But in the unlikelihood that it is, a gracefully
     * close and nullification of internal resources should be attempted.
     * 
     * @return void
     */
    public function __destruct()
    {
        if ($this->connected) {
            $this->closeCpanelHandle();
        }
    }
    /**
     * Enforce an Exception error for undefined methods.
     * 
     * NOTE: This is required for correct error reporting in the Cpanel Service
     * inheritance model.
     * 
     * @param string $method Method invoked
     * @param array  $args   Method arguments
     * 
     * @return void
     * @throws Exception anytime an undefined method is invoked
     */
    public function __call($method, $args)
    {
        throw new Exception("Undefined method $method");
    }
}
?>