<?php
/**
 * Cpanel_Parser_LiveJSON
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
 * @package   Cpanel_Parser
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, Inc., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.1.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
/**
 * LiveJSON Parser
 *
 * @class     Cpanel_Parser_LiveJSON
 * @category  Cpanel
 * @package   Cpanel_Parser
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, Inc., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.1.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
class Cpanel_Parser_LiveJSON extends Cpanel_Core_Object implements Cpanel_Parser_Interface
{
    /**
     * Value for standard JSON print; no additional whitespace or line breaking
     * characters
     */
    const CONDENSED_MODE = 1;
    /**
     * Value for JSON print with whitespace and line breaking characters
     */
    const EXPANDED_MODE = 2;
    /**
     * Response format type that this parser can encode/decode
     */
    const PARSER_TYPE = 'LiveJSON';
    /**
     * Generic prefix for parser error messaging
     */
    const ERROR_DECODE = 'JSON Decode - ';
    /**
     * Observed encode/decode error
     */
    private $_hasParseError;
    /**
     * Constructor
     * 
     * By default, Cpanel_Parser_LiveJSON::CONDENSED_MODE will be set
     * 
     * @param arrays $optsArray Optional configuration data
     * 
     * @return Cpanel_Parser_LiveJSON
     */
    public function __construct($optsArray = array())
    {
        parent::__construct($optsArray);
        $mode = $this->getOption('mode');
        $this->setMode(self::CONDENSED_MODE);
        if ($mode) {
            $this->setMode($mode);
        }
        return $this;
    }
    /**
     * Determine if an RFT can be parsed with this parser
     * 
     * @param string $type The name of a response format type to evaluate
     * 
     * @see    Cpanel_Parser_Interface::canParse()
     * 
     * @return bool Whether this parser can parse a sting of $type
     */
    public function canParse($type)
    {
        return (strtolower($type) == 'livejson') ? true : false;
    }
    /**
     * Parse a string into an array structure
     * 
     * NOTE: Due to differences in API1 and API2 responses, the parsed array
     * will be normalized so that all array representations of the response will
     * have a "cpanelresult" key and the actual array(ified) response as it's
     * value.
     * 
     * NOTE: After basic validation, if the parser cannot successfully parse
     * $str, internal property "_hasParseError" will be set to true.  An error
     * will NOT be throw.  This is to ensure a premature exit does not occur
     * since the query (likely) succeeded.  Problem as this level are ambiguous,
     * and therefore left to the invoking script/application to manage.
     * 
     * @param string $str String to parse
     * 
     * @see    Cpanel_Parser_LiveJSON::getParserInternalErrors
     * @see    Cpanel_Parser_Interface::parse()
     * 
     * @return array|string Array representation of string on success,
     *                      otherwise a string expressing error.
     * @throws Exception If $str is not a string
     */
    public function parse($str)
    {
        if (!is_string($str)) {
            throw new Exception('Input must be a raw response string');
        } elseif (empty($str)) {
            $this->_hasParseError = true;
            return $this->getParserInternalErrors(
                self::ERROR_DECODE, 'Cannot decode empty string.'
            );
        }
        $json_start_pos = strpos($str, "<cpanelresult>{");
        if ($json_start_pos === false) {
            $this->_hasParseError = true;
            return $this->getParserInternalErrors(
                self::ERROR_DECODE,
                'Invalid server response string for LiveJSON parser.'
            );
        }
        $json_start_pos+= 14;
        $parsed = json_decode(
            trim(
                substr(
                    trim($str),
                    $json_start_pos,
                    strpos($str, "</cpanelresult>") - $json_start_pos
                )
            ),
            true
        );
        if (is_null($parsed)) {
            $this->_hasParseError = true;
            return $this->getParserInternalErrors(self::ERROR_DECODE);
        }
        if (strpos($str, '<cpanelresult>{"cpanelresult"') === false) {
            // need for compat: api2 tags will end up with dup due to internals
            // $json_start_pos = strpos( $result, "<cpanelresult>" ) + 14;
            return array(
                'cpanelresult' => $parsed
            );
        } else {
            return $parsed;
        }
    }
    /**
     * Encode array structure into this parser's format type
     * 
     * If Cpanel_Parser_JSON::EXPANDED_MODE was set via {@link setMode()}
     * then the returned string will have excessive whitespace and line breaking
     * characters
     * 
     * NOTE: The return string is will be the JSON value of the original
     * server response, wrapped in the "<cpanelresult></cpanelresult>" tags as
     * that is what a raw LiveJSON response looks like.  This is NOT XML!  They
     * are simple markers/tags used by the Live response protocol.
     *  
     * NOTE: unlike {@link parse()}, this method WILL throw an Exception If
     * encoding fails.  There should be no ambiguity, if the response objects
     * "_response" container has invalid data, there is a problem (that needs
     * to be addressed by the script/application developer).
     *  
     * @param Cpanel_Query_Object $obj Response object containing data to
     *  encode
     * 
     * @see    Cpanel_Parser_Interface::encodeQueryObject()
     * 
     * @return string              JSON string
     * @throws Exception If $obj is an invalid response object
     * @throws Exception If encoding fails
     */
    public function encodeQueryObject($obj)
    {
        if (!($obj instanceof Cpanel_Query_Object)) {
            throw new Exception('Parser can only encode known query object');
        }
        $arr = $obj->getResponse('array');
        $jsonStr = '';
        $msg = '';
        try {
            $jsonStr = json_encode($arr);
        }
        catch(Exception $e) {
            $msg = ". " . $e->getMessage();
            $msg.= '. Store data likely contains a resource'
                 . ' or non-UTF8 string.';
        }
        if (!empty($msg) || empty($jsonStr)) {
            throw new Exception('JSON encoding error for ' . __FUNCTION__ . $msg);
        }
        if ($this->mode == self::EXPANDED_MODE) {
            $jsonStr = $this->prettyPrint($jsonStr);
        }
        return "<cpanelresult>$jsonStr</cpanelresult>";
    }
    /**
     * Encode a query object for use in a Live cpanelaction call
     * 
     * NOTE: For use by service adapters when making local queries
     * 
     * NOTE: This method will store the cpanelaction tag within the query
     * object's "_query" container at LiveJSON, ie $rObj->query->LiveJSON = $str
     * 
     * @param Cpanel_Query_Object $rObj Query object to source and
     *                                   update data as necessary
     * 
     * @return string              A cpanelaction tag for use in a Live local query
     * @throws Exception If $obj is an invalid response object
     * @throws Exception If encoding fails
     */
    public function encodeQuery($rObj)
    {
        if (!($rObj instanceof Cpanel_Query_Object)) {
            throw new Exception('Parser can only encode known query object');
        }
        $query = array(
            'reqtype' => $rObj->query->reqtype,
            'module' => $rObj->query->module,
            'func' => $rObj->query->func,
            'apiversion' => $rObj->query->apiversion,
        );
        $args = $rObj->query->args;
        if (is_object($args) && ($args instanceof Cpanel_Core_Object)) {
            $query['args'] = $args->getAllDataRecursively();
        } elseif (!empty($args)) {
            $query['args'] = $args;
        }
        $jsonStr = '';
        try {
            $jsonStr = json_encode($query);
        }
        catch(Exception $e) {
            $msg = $e->getMessage();
            $msg.= '. Query data likely contains a resource or non-UTF8 string.';
            throw new Exception(
                'JSON encoding error for ' . __FUNCTION__ . ". {$msg}"
            );
        }
        $encodedStr = "<cpanelaction>\n{$jsonStr}\n</cpanelaction>";
        $store = self::PARSER_TYPE;
        $rObj->query->$store = $encodedStr;
        return $encodedStr;
    }
    /**
     * Select out the simple result string
     * 
     * This is primarily for use by legacy methods within
     * Cpanel_Service_Adapter_Liveapi
     * 
     * @param Cpanel_Query_Object $rObj Response object
     * 
     * @return mixed Value stored in the data->result node of server response
     */
    public function getLegacyString($rObj)
    {
        if ($rObj->validResponse()) {
            return $rObj->cpanelresult->data->result;
        } else {
            return null;
        }
    }
    /**
     * Pretty-print JSON string
     *
     * Use 'indent' option to select indentation string - by default it's a tab
     *
     * @param string $json    Original JSON string
     * @param array  $options Encoding options
     * 
     * @return    string
     *                   
     * @category  Zend
     * @package   Zend_Json
     * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     * @version   $Id: Json.php 23775 2011-03-01 17:25:24Z ralph $
     */
    public static function prettyPrint($json, $options = array())
    {
        $tokens = preg_split('|([\{\}\]\[,])|', $json, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = "";
        $indent = 0;
        $ind = "\t";
        if (isset($options['indent'])) {
            $ind = $options['indent'];
        }
        foreach ($tokens as $token) {
            if ($token == "") continue;
            $prefix = str_repeat($ind, $indent);
            if ($token == "{" || $token == "[") {
                $indent++;
                if ($result != "" && $result[strlen($result) - 1] == "\n") {
                    $result.= $prefix;
                }
                $result.= "$token\n";
            } else if ($token == "}" || $token == "]") {
                $indent--;
                $prefix = str_repeat($ind, $indent);
                $result.= "\n$prefix$token";
            } else if ($token == ",") {
                $result.= "$token\n";
            } else {
                $result.= $prefix . $token;
            }
        }
        return $result;
    }
    /**
     * Generate an error string to bubble up
     * 
     * If running on PHP >= 5.3, json_last_error() will be used to retrieve any
     * relevant errors
     * 
     * @param string $prefix  A string to prefix the returned error string for 
     *                         contextual reference
     * @param string $default A default error message if one can not be 
     *                         determined (from the native PHP JSON functions)
     * 
     * @see    Cpanel_Parser_Interface::getParserInternalErrors()
     * 
     * @return string String detailing an error has occurred
     */
    public function getParserInternalErrors($prefix = '', $default = ' Could not load string')
    {
        $errmsg = '';
        if ($this->_hasParseError) {
            $errmsg.= $prefix;
            if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
                switch (json_last_error()) {
                case JSON_ERROR_DEPTH:
                    //Parse do not support setting depth, but it's here anyway
                    //@codeCoverageIgnoreStart
                    $errmsg.= 'Maximum stack depth exceeded.';
                    break;
                    //@codeCoverageIgnoreEnd
                    
                case JSON_ERROR_CTRL_CHAR:
                    $errmsg.= 'Unexpected control character found.';
                    break;

                case JSON_ERROR_SYNTAX:
                    $errmsg.= 'Syntax error, malformed JSON.';
                    break;

                case JSON_ERROR_STATE_MISMATCH:
                    $errmsg.= 'Invalid or malformed JSON.';
                    break;
                    //@todo with 5.3.3 add JSON_ERROR_UTF8 to switch
                    
                case JSON_ERROR_NONE:
                default:
                    $errmsg.= $default;
                }
                //@codeCoverageIgnoreStart
                
            } else {
                $errmsg.= $default;
            }
            //@codeCoverageIgnoreEnd
            
        }
        return $errmsg;
    }
    /**
     * Set special encode mode for JSON
     * 
     * @param int $flag Constant value to set encoding mode to
     * 
     * @see    Cpanel_Parser_LiveJSON::CONDENSED_MODE
     * @see    Cpanel_Parser_LiveJSON::EXPANDED_MODE
     * 
     * @return Cpanel_Parser_LiveJSON
     */
    public function setMode($flag)
    {
        if (empty($flag) || !is_int($flag)) {
            $this->mode = self::CONDENSED_MODE;
        } elseif ($flag == self::CONDENSED_MODE || $flag == self::EXPANDED_MODE) {
            $this->mode = $flag;
        }
        return $this;
    }
}
?>