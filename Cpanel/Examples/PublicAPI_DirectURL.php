<?php
/**
 * Example usage of Direct URL query via PublicAPI client
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
 * Please look at the Introduction_to_PublicAPI.php for more details.
 */
TRY {
    require_once realpath( dirname(__FILE__) . '/../Util/Autoload.php');
    
    /**
     * Direct URL queries will fetch a given URL for the server.  The response 
     * object that is return will only have the raw response string stored.  The
     * is no attempt to parse the result, as it is assumed that the developer is
     * make a direct URL query due to one of the following: 
     * 
     * A) Is triggering a page that performs action, for which there is no API
     *  call and the output is irrelevant
     * B) The output of the page either cannot be parsed programmatically or 
     *  does not need to be parsed by a parser.
     *  
     * The result of this behavior is that the returned object is not directly 
     * iterable.  The raw response can be accessed, just like any response 
     * object:
     * $response = $cp->api_request($service, $url, $httpQueryMethod, $formdata, $customHeaders);
     * $rawResponse = $response->getRawResponse();
     * 
     * Or, the developer can attempt to coerce the result with parser object
     * instantiated from the library:
     * // make the call and get a raw response...
     * 
     * $newResponse = new Cpanel_Query_Object();
     * $newResponse->setResponseFormatType('JSON');
     * $newResponse->parse($rawResponse);
     * if($newResponse->validResponse()) ){
     *     //do stuff
     * }
     * 
     */
    
    $whmCfg = array(
        'host' => '10.1.4.102',
        'user' => 'root',
        'password' => 'rootsecret'
    );
    $masterCfg = array(
        'service' => array(
            'whm' => array(
                'config' => $whmCfg
            )
        ),
    );
    
    $cp = Cpanel_PublicAPI::getInstance($masterCfg);
    
    /**
     * To make a direct URL query, use the "api_request()" method of the
     * PublicAPI client.
     * 
     * api_request() has five parameters
     *  * $service
     *     - The Service to use, this will determine the port to use
     *  * $url
     *     - The URL to poll
     *  * $httpQueryMethod (Optional)
     *     - A string, either 'GET' or 'POST'. Default is 'GET'
     *  * $formdata ($Optional)
     *     - An array or string of any URL parameters to use in the URL query
     *  * $customHeaders (Optional)
     *     - An array of custom headers to place in the HTTP HEADER.  It's
     *     unlikely that the developer needs to use this parameter.
     *     
     * The following example illustrates making a direct URL query against
     * $server:2087/xml-api/version
     */
    
    $url = '/xml-api/version';
    $formdata = '';
    
    $response = $cp->api_request(
        'WHM',
        $url,
        'GET',
        $formdata
    );
    
    $raw = $response->getRawResponse();
    
    /**
     * The raw response can be parse in any way the developer sees fit.
     * 
     * Since the direct URL for '/xml-api/version' returns XML, we can use
     * the PHP SimpleXML functions to decode the string
     */
    $simplxml = simplexml_load_string($raw);
    echo "WHM Version: {$simplxml->version}\n";
    
    
    /**
     * The following example illustrates a direct URL query against
     * $server:2087/json-api/listaccts?serchtype=domain&search=dave.com
     * and passing a custom header 'CustomHeader=CustomSendValue'
     */
    $url = '/json-api/listaccts';
    $formdata = array(
        'searchtype' => 'domain',
        'search' => 'dave.com'
    );
    
    $response = $cp->api_request(
        'WHM',
        $url,
        'GET',
        $formdata,
        array(
            'CustomHeader' => 'CustomSendValue'
        )
    );
    
    $raw = $response->getRawResponse();
    
    /**
     * Again, decode appropriately.
     * 
     * Here, we'll use the cPanel PHP library's JSON parser class
     */
    $newResponse = new Cpanel_Query_Object();
    $newResponse->setResponseFormatType('JSON');
    $newResponse->parse($raw);
    
    echo "Dave's Domain Detail:\n";
    foreach ($newResponse->acct as $acct) {
        foreach ($acct as $key => $value) {
            echo "\t{$key}: {$value}\n";
        }
    }
}
CATCH(Exception $e) {
    echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
echo "EOF: You've successfully ended the " . basename(__FILE__) . " script.\n";
?>