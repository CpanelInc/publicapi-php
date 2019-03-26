<?php
/**
 * Cpanel_Query_Object
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
 * @package   Cpanel_Query
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.1.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
/**
 * Query and response object class
 * 
 * This class works as both a request (or query) and response object in the
 * since of most PHP frameworks. There are two containers for these
 * responsibilities (query and response). Internally they are '_query' and
 * '_response' but can be accessed externally simply by fetching 
 * $rObj->getQuery() or $rObj->getResponse().  Alternatively, there's a shortcut
 * for fetching individual key/value pairs within those containers.  If 
 * $rObj->key is called, $key will be fetched out of the response container. If
 * $rObj->query->key is called, $key will be fetched from the query container.
 * Setting values works in the same manner.
 *
 * @class     Cpanel_Query_Object
 * @category  Cpanel
 * @package   Cpanel_Query
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.1.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
class Cpanel_Query_Object extends Cpanel_Core_Object
{
    /**
     * Container space for query data
     */
    private $_query;
    /**
     * Container space for server response data
     */
    private $_response;
    /**
     * Raw server response string
     */
    private $_rawResponse = null;
    /**
     * Generic Parser interface for parse object validation
     */
    private $_pinterface;
    /**
     * Input format
     */
    private $_inputFormat;
    /**
     * Default output format
     */
    private $_defaultOutputFormat = 'Cpanel_Core_Object';
    /**
     * Output format
     */
    private $_outputFormat;
    /**
     * Response format type (RFT)
     */
    private $_responseFormat;
    /**
     * Parser object
     */
    private $_responseParser;
    /**
     * Error container
     */
    private $_responseErrors = array();
    /**
     * Prefix for error message
     */
    const ERROR_RESPONSE = 'Response Error: ';
    /**
     * Generic error message
     */
    const ERROR_PARSE = 'Could not parse.';
    /**
     * Constructor
     * 
     * @param arrays $optsArray Optional configuration data
     * 
     * @return Cpanel_Query_Object
     */
    public function __construct($optsArray = array())
    {
        parent::__construct($optsArray);
        $this->_query = new Cpanel_Core_Object();
        //TODO: make response an arrayobject with magic accessors?
        $this->_response = new Cpanel_Core_Object();
        //@todo make meths of this?
        $this->_pinterface = 'Cpanel_Parser_Interface';
        $this->_outputFormat = $this->_defaultOutputFormat;
        return $this;
    }
    /**
     * Set server expected input format
     * 
     * @param string $type Input format type
     * 
     * @return Cpanel_Query_Object
     */
    public function setInputFormatType($type)
    {
        $this->_inputFormat = $type;
        return $this;
    }
    /**
     * Retrieve input format type
     * 
     * @return string Input format type
     */
    public function getInputFormatType()
    {
        return $this->_inputFormat;
    }
    /**
     * Set response format type
     * 
     * The response format type (RFT) is the format of the server response. This
     * value is propagated to the stored parser object (generating a new parser
     * object if one was not previous instantiated)
     * 
     * @param string $type   Response format type
     * @param bool   $reinit Force a new parser object instantiation prior to
     *  setting RFT
     * 
     * @return Cpanel_Query_Object
     * @throws Exception If parser object does not implement the defined parser
     *  interface
     * @throws Exception If parser object can not parse the requested RFT
     */
    public function setResponseFormatType($type, $reinit = false)
    {
        $this->_responseFormat = $type;
        $p = $this->_responseParser;
        if (!$p || $reinit) {
            $this->setResponseParser($this->getValidParser($type));
        } else {
            if (!($p instanceof $this->_pinterface)) {
                throw new Exception('Response parser must implement' . $this->_pinterface);
            }
            if (!$p->canParse($type)) {
                $c = get_class($p);
                throw new Exception("$c cannot parse response type $type");
            }
        }
        return $this;
    }
    /**
     * Instantiate a parser object.
     * 
     * This method expects $type to coorelate to a Cpanel parser class located
     * within Cpanel_Library.  If a Cpane parser class cannot be found, $type
     * will be treated as a custom class that has previously been defined (or
     * available via the autoload stack).  Custom parser classes must implement
     * Cpanel_Parser_Interface
     * 
     * @param string $type Name of parser to instantiate
     * 
     * @return mixed     Parser object which implements Cpanel_Parser_Interface
     * @throws Exception If $type cannot be tranlated to a valid classname for
     *                   instantiation
     * @throws Exception If instantiated parser object does not implement
     *                   Cpanel_Parser_Interface
     */
    public function getValidParser($type)
    {
        $class = "Cpanel_Parser_{$type}";
        /**
         * Depending on the autoloader, class_exist may issue a warning however
         * we loose alot of flexibility if we don't call __autoload, if you find
         * this a problem, alter your error reporting function accordingly
         */
        if (class_exists($class)) {
            $c = $class;
        } elseif (class_exists($type)) {
            $c = $type;
        } else {
            throw new Exception(
                "{$type} is an invalid parser and class is not defined"
            );
        }
        $parserOptions = $this->parserOptions;
        $parserOptions = (empty($parserOptions)) ? array() : $parserOptions;
        $p = new $c($parserOptions);
        if (!($p instanceof $this->_pinterface)) {
            throw new Exception("{$c} must implement {$this->_pinterface}");
        }
        return $p;
    }
    /**
     * Store a parser object
     * 
     * @param object $obj Parser object that implements Cpanel_Parser_Interface
     * 
     * @return Cpanel_Query_Object
     */
    public function setResponseParser($obj)
    {
        if (!($obj instanceof $this->_pinterface)) {
            throw new Exception(
                'Response parser must implement ' . $this->_pinterface
            );
        }
        $this->_responseParser = $obj;
        return $this;
    }
    /**
     * Retrieve the stored parser object
     * 
     * @return mixed Object that implements Cpanel_Parser_Interface
     */
    public function getResponseParser()
    {
        return $this->_responseParser;
    }
    /**
     * Retrieve the response format type defined in this object
     * 
     * @return string
     */
    public function getResponseFormatType()
    {
        return $this->_responseFormat;
    }
    /**
     * Set oupt format for this object
     * 
     * @param string $type Output format type
     * 
     * @return Cpanel_Query_Object
     */
    public function setOutputFormatType($type)
    {
        $this->_outputFormat = $type;
        return $this;
    }
    /**
     * Retrieve this object's output format type
     * 
     * @return string Output format type
     */
    public function getOutputFormatType()
    {
        return $this->_outputFormat;
    }
    /**
     * Retrieve a representation of the server response as requested by $type
     * 
     * The method, by default will return the response container, a
     * Cpanel_CpanelObject which is an iterable object.  Other options include:
     * 'array'  - An deeply nested array representation of the server response
     * "$type"  - Where $type is an RFT for which a parser can be instantiated,
     *   i.e. 'JSON' or 'XML', etc.
     * 
     * @param string $type Response format type to return
     * 
     * @return mixed  String, array, or object as requested by $type
     */
    public function getResponse($type = '')
    {
        $type = ($type) ? $type : $this->getOutputFormatType();
        if ($type == $this->_defaultOutputFormat) {
            return $this;
        } elseif (strtolower($type) == 'array') {
            return $this->_response->getAllDataRecursively();
        } elseif ($type == $this->getResponseFormatType()) {
            return $this->getRawResponse();
        } else {
            $parser = $this->getValidParser($type);
            // TODO: consider try/catch and pushing into error stack, similar
            //   to a $parser::parse()
            return $parser->encodeQueryObject($this);
        }
    }
    /**
     * Store error in internal error stack
     * 
     * @param string|array $err Error message string or array of such strings
     * 
     * @return Cpanel_Query_Object
     */
    protected function setResponseError($err)
    {
        if (is_string($err)) {
            array_push($this->_responseErrors, $err);
        } elseif (count($err)) {
            foreach ($err as $e) {
                array_push($this->_responseErrors, $e);
            }
        }
        return $this;
    }
    /**
     * Check if parser object reported errors
     * 
     * @return bool Whether the parser object was able to parser server response
     */
    public function validResponse()
    {
        if (!empty($this->_responseErrors)) {
            return false;
        }
        return true;
    }
    /**
     * Retrieve all error reported by parser
     *  
     * @param bool $flush Flush internal error stack.  Default is FALSE.
     * 
     * @return array|void An array of error string, otherwise null if no errors
     *                    were reported by the parser object
     */
    public function getResponseErrors($flush = false)
    {
        $r = $this->_responseErrors;
        if (empty($r)) {
            $r = null;
        } else {
            if ($flush) {
                $this->_responseErrors = array();
            }
        }
        return $r;
    }
    /**
     * Set multiple key/value pairs in the query container
     * 
     * @param array $obj An array of key/value pairs to set
     * 
     * @return Cpanel_Query_Object
     */
    public function setQuery($obj)
    {
        $this->_query->setOptions($obj);
        return $this;
    }
    /**
     * Retrieve the query container
     * 
     * @return Cpanel_Core_Object
     */
    public function getQuery()
    {
        return $this->_query;
    }
    /**
     * Parse and store a raw response
     * 
     * This method is responsible for receiving a raw server response, storing
     * it, passing the raw response to the parser object and finally storing
     * the parse response.  If parsing errors occur, the value of the response
     * contain will be empty and errors can be collected via 
     * {@link getResponseErrors()}
     * 
     * @param string $raw The raw response string from the server
     * 
     * @see    getResponseErrors()
     * 
     * @return Cpanel_Core_Object
     */
    public function parse($raw)
    {
        $this->setRawResponse($raw);
        if ($this->_query->directURL === true) {
            $parsedResponse = array();
        } else {
            $parsedResponse = $this->_parseWithParser();
            if (!is_array($parsedResponse)) {
                if ($parsedResponse === false) {
                    //@codeCoverageIgnoreStart
                    $this->setResponseError(
                        self::ERROR_RESPONSE . self::ERROR_PARSE
                    );
                    //@codeCoverageIgnoreEnd
                    
                } elseif (is_string($parsedResponse)) {
                    $this->setResponseError(
                        self::ERROR_RESPONSE . $parsedResponse
                    );
                }
                $parsedResponse = array();
            }
        }
        $this->setResponse($parsedResponse);
        return $this->getResponse();
    }
    /**
     * Trigger parser object's parse() against server's raw response
     * 
     * @return array|string Array representation of raw response, otherwise a
     *                      string denoting error observed by parser
     * @throws Exception If parser object does not implement
     *                      Cpanel_Parser_Interface
     */
    private function _parseWithParser()
    {
        $raw = $this->getRawResponse();
        $p = $this->getResponseParser();
        if (!$p || !($p instanceof $this->_pinterface)) {
            throw new Exception('Invalid response parser');
        }
        return $p->parse($raw);
    }
    /**
     * Store key/value pairs in response container
     * 
     * @param array $obj Array of key/value pairs to set
     * 
     * @return Cpanel_Query_Object
     */
    public function setResponse($obj)
    {
        $this->_response->setOptions($obj);
        return $this;
    }
    /**
     * Retrieve raw server response
     * 
     * @return string Raw server response string
     */
    public function getRawResponse()
    {
        return $this->_rawResponse;
    }
    /**
     * Store raw server response
     * 
     * @param string $str String to store as the raw server response
     * 
     * @return Cpanel_Query_Object
     */
    public function setRawResponse($str = null)
    {
        $listner = $this->getOption('listner');
        if ($listner) {
            $listner->log('debug', "Storing RawResponse:\n$str");
        }
        $this->_rawResponse = $str;
        return $this;
    }
    /**
     * Magic get accessor
     * 
     * If $key is 'query', the query container is returned.  This allows for 
     * effective chain calls like $rObj->query->ulitamteKeyDesired in calling
     * scripts.  Otherwise, $key will source from the response container
     * 
     * @param string $key Key to search for
     * 
     * @return mixed  Value sourced for $key within the response container, or
     *  the query container if $key is 'query'
     */
    public function __get($key)
    {
        if ($key == 'query') {
            return $this->_query;
        } else {
            return $this->_response->getOption($key);
        }
    }
    /**
     * Magic set accessor
     * 
     * The method will store key/value pairs within the response container. If
     * storage to the query contain is desired, use {@link setQuery()} or a
     * combination of magic {@__get()} methods, i.e. $rObj->query->key = value
     * 
     * @param string $key   Key for storage
     * @param mixed  $value Value to associate with $key
     * 
     * @return void  
     */
    public function __set($key, $value)
    {
        $this->setResponse(
            array(
                $key => $value
            )
        );
    }
    /**
     * Magic toString method
     * 
     * This method will return a formatted representation of the response 
     * container (i.e. a print_r style string) when this object is referenced as
     * or cased to a string
     * 
     * @return string String representation of the response container object
     */
    public function __toString()
    {
        $str = print_r($this->_response, true);
        return $str;
    }
}
?>