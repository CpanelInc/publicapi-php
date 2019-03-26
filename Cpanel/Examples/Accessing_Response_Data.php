<?php
/**
 * Example usage of iterating over a response (Cpanel_Query_Object)
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
 * @package   Cpanel_PublicAPI
 * @author    David Neimeyer <david.neimeyer@cpanel.net>
 * @copyright Copyright (c) 2011, cPanel, L.L.C., All rights Reserved. (http://cpanel.net) 
 * @license   http://sdk.cpanel.net/license/bsd.html BSD License 
 * @version   0.2.0
 * @link      http://sdk.cpanel.net
 * @since     0.1.0
 */

/**
 * Please look at the Introduction_to_PublicAPI.php for more details.
 */
TRY {
    require_once realpath( dirname(__FILE__) . '/../Util/Autoload.php');
    
    /**
     * The PublicAPI client and Service objects always return a
     * Cpanel_Query_Object. It's a iterable object.  This response object will
     * be a deeply nested structure.  Iterating over the response will traverse
     * the structure. 
     * 
     * A Cpanel_Query_Object contains three containers.  First, the 'response'
     * container.  This will be accessed by default when the object is treated
     * as an iterable or under normal property lookup:
     * 
     * $copy_of_everything_in_root_node = $response->rootnode
     * 
     * The second container is the 'query' space.  It houses all the data used
     * to make the request.  It's contents can be accessed by getting the
     * 'query' property:
     * 
     * $query_container = $response->query;
     * 
     * Anytime a property is accessed one of two value types will be returned:
     *  * String
     *    - when the properties value is a string or integer
     *  * Cpanel_Query_Object
     *    - when the properties value is a data structure
     *      - in such an instance, if an array (not an object) is necessary,
     *        you can invoke getAllDataRecursively() and it will return the
     *        array respresentation of that embedded object.
     * 
     * The last container is the 'raw response' container.  The value in this
     * container is the raw string returned from the request.  It can be
     * be accessed as follows:
     * 
     * $raw = $response->getRawResponse();
     * 
     * 
     * NOTE: Because all remote Service adapters use JSON, any API2 or XML-API
     * function that may return a list, should, in fact, have the a list context
     * for the respective node.  Please see this forum post for a more indepth
     * discussion: 
     * http://forums.cpanel.net/f145/api-improving-return-arrays-170090.html 
     * 
     * 
     * Other imporant methods of the Cpanel_Query_Object class:
     *  * validResponse()
     *    - Boolean value that denotes if the raw response was parsed and
     *      available in the 'response' container
     *  * getResponseErrors()
     *    - An array populated with error messages generated by the parser.
     *      An empty array will be returned if no errors are present.
     *      
     * NOTE: The above two methods do not account for authentication errors due
     * to bad credentials or the state of the requested action (as determined by
     * the cPanel system).  In both cases, the raw response from the cPanel
     * system should be well structured and parsed cleanly (i.e., can be
     * determined by analysing the data in the 'response' container).
     * 
     * Future implementation of error handling and the Cpanel_Query_Object
     * may see some programmatic improvement for this, like throwing a special
     * Exception, however, for now it is up to the programmer to either 1) check
     * the response container for such failures, or 2) extend the parser library
     * with such logic.
     * 
     * Other convenience methods are described below
     */
    
    $configOpts = array(
        'username' => 'root',
        'password' => 'rootsecret',
    );
    $services = array(
        'whm' => array(
            'host' => '10.1.4.102',
            'user' => 'root',
            'password' => 'rootsecret',
        ),
    );
    $masterConfig = array(
        'config' => $configOpts,
        'service' => $services,
    );
    
    $cp = Cpanel_PublicAPI::getInstance($masterConfig);
    
    
    $response = $cp->whm_api('listaccts');
    
    /**
     * A quick check to see that the server response parsed cleanly.
     */
    if (!$response->validResponse()) {
        $errors = $response->getResponseErrors();
        foreach ($errors as $err) {
            // do something like log or throw Exception
        }
        return;
    }
    
    /**
     * If authentication failed, there's likely to be a data->reason parsed
     * response
     */
    if ($response->data && !$response->data->result) {
        
        if (strpos($response->data->reason, 'Access denied') !== false) {
            echo "Could not authenticate!\n";
            // do something important here
            return;
        } else {
            echo "Unknown failure: {$response->data->reason}";
            // do something important here
            return;
        }
    }
    
    /**
     * Since we made an XMl-API function call, we should be able to check that
     * the cPanel system executed the call
     */
    $statusmsg = $response->statusmsg;
    $status = $response->status;
    if (!$status) {
        echo "Bad Status: {$status}\n\t{$statusmsg}\n";
        return;
    } else {
        echo "Responses status: {$status}\n";
    }
    
    /**
     * The listaccts XML-API function returns a parent node with one or more
     * nodes named 'acct'.  So, we can iterate of 'acct':
     */
    echo "The WHM server has the following accounts and domains:\n";
    foreach ($response->acct as $acctDetails) {
        /**
         * Fetch loop on response
         * -parent_node
         *   -acct
         *      -user:   $firstAcctValueUser
         *      -domain: $firstAcctValueDomain
         *   -acct
         *      -user:   $secondAcctValueUser
         *      -domain: $secondAcctValueDomain
         */
        echo "\tUser: {$acctDetails->user}\tDomain: {$acctDetails->domain}\n";
    }
 
    echo "\n";
    
    
    /**
     * The Cpanel_Query_Object can also encode the response into a known type.
     * 
     * Currently, Cpanel_Query_Object can encode to XML, JSON, a native PHP
     * array, or the special LiveJSON format (used by the cPanel's LivePHP).
     */
    $xml = $response->getResponse('XML');
    echo "This is the response in XML form:\n{$xml}\n\n";
    
    $json = $response->getResponse('JSON');
    echo "This is the response in JSON form:\n{$json}\n\n";
    
    $PHParray = $response->getResponse('array');
    echo "This is the response in PHP array form:\n" 
        . print_r($PHParray, true) . "\n\n";
    
}
CATCH(Exception $e) {
    echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
echo "EOF: You've successfully ended the " . basename(__FILE__) . " script.\n";
?>