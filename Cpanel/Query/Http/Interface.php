<?php
/**
 * Cpanel_Query_Http_Interface
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
 * Interface for all HTTP query classes
 *
 * @class      Cpanel_Query_Http_Interface
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
interface Cpanel_Query_Http_Interface extends Cpanel_Query_Interface
{
    /**
     * Proxy method for PHP's curl function set
     * 
     * @param Cpanel_Query_Object $obj Response object to source and update
     * data as necessary for the current query
     * 
     * @return string Raw server response string
     */
    function curlQuery($obj);
    /**
     * Proxy method for PHP's fopen function set
     * 
     * @param Cpanel_Query_Object $obj Response object to source and update
     * data as necessary for the current query
     * 
     * @return string Raw server response string
     */
    function fopenQuery($obj);
    /**
     * Return an HTTP authentication header string
     * 
     * @return string Authentication header string based on internal properties 
     */
    function buildAuthStr();
    /**
     * Build a query URL from the provided $function and internal properties
     * 
     * @param string $function Remote function to call
     * @param bool   $isURL    if $function is a partial URL {@link isURL()}
     * 
     * @return string Constructed URL string, including protocol, host, port and
     *                function; no arguments to the function are present 
     */
    function buildURL($function, $isURL = false);
    /**
     * Determines if $data is a URL parameter string, i.e. key=value&key2=value2
     * 
     * @param mixed $data   String or Array to evaluate. Arrays will always eval
     *  to FALSE.
     * @param bool  $strict If $data is a String & this parameter is TRUE,
     *  additional logic is applied to determine if $data is similar to an http
     *  query string
     * 
     * @return bool 
     */
    function isURLParamStr($data, $strict = false);
    /**
     * Determine if $str is, at minimum, a partial URL
     * 
     * @param string $str String to evaluate
     * 
     * @return bool  
     */
    function isURL($str);
}
?>