<?php
/**
 * Cpanel_Parser_Interface
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
 * Interface class for parsers
 *
 * @class     Cpanel_Parser_Interface
 * @category  Cpanel
 * @package   Cpanel_Parser
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.2.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */
interface Cpanel_Parser_Interface
{
    /**
     * Determine if an RFT can be parsed with this parser
     * 
     * @param string $type The name of a response format type to evaluate
     * 
     * @return bool   Whether this parser can parse a sting of $type
     */
    function canParse($type);
    /**
     * Parse a string into an array structure
     * 
     * @param string $str String to parse
     * 
     * @return array|string Array representation of string on success,
     *                      otherwise a string expressing error.
     */
    function parse($str);
    /**
     * Encode array structure into this parser's format type
     * 
     * @param Cpanel_Query_Object $obj Response object containing data to
     *  encode
     * 
     * @return string             
     */
    function encodeQueryObject($obj);
    /**
     * Generate an error string to bubble up
     * 
     * @param string $prefix  A string to prefix the returned error string for 
     *                         contextual reference
     * @param string $default A default error message if one can not be 
     *                         determined
     * 
     * @return string String detailing an error has occurred
     */
    function getParserInternalErrors($prefix = '', $default = '');
}
?>