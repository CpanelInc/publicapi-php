<?php
/**
 * Cpanel_Parser_JSON
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
 * @package   Cpanel_Parser
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.2.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
/**
 * JSON Parser
 *
 * @class     Cpanel_Parser_JSON
 * @category  Cpanel
 * @package   Cpanel_Parser
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.2.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
class Cpanel_Parser_JSON extends Cpanel_Core_Object
    implements Cpanel_Parser_Interface
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
    const PARSER_TYPE = 'JSON';
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
     * By default, Cpanel_Parser_JSON::CONDENSED_MODE will be set
     * 
     * @param array $optsArray Optional configuration data
     * 
     * @return Cpanel_Parser_JSON
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
     * @return bool   Whether this parser can parse a sting of $type
     */
    public function canParse($type)
    {
        return (strtolower($type) == 'json') ? true : false;
    }
    /**
     * Parse a string into an array structure
     * 
     * NOTE: After basic validation, if the parser cannot successfully parse
     * $str, internal property "_hasParseError" will be set to true.  An error
     * will NOT be throw.  This is to ensure a premature exit does not occur
     * since the query (likely) succeeded.  Problem as this level are ambiguous,
     * and therefore left to the invoking script/application to manage.
     * 
     * @param string $str String to parse
     * 
     * @see    Cpanel_Parser_JSON::getParserInternalErrors
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
        $r = json_decode($str, true);
        if (is_null($r)) {
            $this->_hasParseError = true;
            return $this->getParserInternalErrors(self::ERROR_DECODE);
        }
        return $r;
    }
    /**
     * Encode array structure into this parser's format type
     * 
     * If Cpanel_Parser_JSON::EXPANDED_MODE was set via {@link setMode()}
     * then the returned string will have excessive whitespace and line breaking
     * characters
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
     * @return string JSON string
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
            $msg.= '. Store data likely contains a resource' . ' or non-UTF8 string.';
        }
        if (!empty($msg) || empty($jsonStr)) {
            throw new Exception('JSON encoding error for ' . __FUNCTION__ . $msg);
        }
        if ($this->mode == self::EXPANDED_MODE) {
            $jsonStr = $this->prettyPrint($jsonStr);
        }
        return $jsonStr;
    }
    /**
     * Generate an error string to bubble up
     * 
     * If running on PHP >= 5.3, json_last_error() will be used to retrieve any
     * relevant errors
     * 
     * @param string $prefix  A string to prefix the returned error string for 
     *  contextual reference
     * @param string $default A default error message if one can not be 
     *  determined (from the native PHP JSON functions)
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
                        $errmsg.= 'The maximum stack depth has been exceeded';
                        break;
                    case JSON_ERROR_STATE_MISMATCH:
                        $errmsg.= 'Invalid or malformed JSON';
                        break;
                    case JSON_ERROR_CTRL_CHAR:
                        $errmsg.= 'Control character error, possibly incorrectly encoded';
                        break;
                    case JSON_ERROR_SYNTAX:
                        $errmsg.= 'Syntax error';
                        break;
                    case JSON_ERROR_UTF8:
                        $errmsg.= 'Malformed UTF-8 characters, possibly incorrectly encoded';
                        break;
                    case JSON_ERROR_RECURSION:
                        $errmsg.= 'One or more recursive references in the value to be encoded';
                        break;
                    case JSON_ERROR_INF_OR_NAN:
                        $errmsg.= 'One or more NAN or INF values in the value to be encoded';
                        break;
                    case JSON_ERROR_UNSUPPORTED_TYPE:
                        $errmsg.= 'A value of a type that cannot be encoded was given';
                        break;
                    case JSON_ERROR_INVALID_PROPERTY_NAME:
                        $errmsg.= 'A property name that cannot be encoded was given';
                        break;
                    case JSON_ERROR_UTF16:
                        $errmsg.= 'Malformed UTF-16 characters, possibly incorrectly encoded';
                        break;
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
     * Set special encode mode for JSON
     * 
     * @param int $flag Constant value to set encoding mode to
     * 
     * @see    Cpanel_Parser_JSON::CONDENSED_MODE
     * @see    Cpanel_Parser_JSON::EXPANDED_MODE
     * 
     * @return Cpanel_Parser_JSON
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