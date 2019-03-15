<?php
/**
 * Example usage of cPanel Service with an API Token
 * 
 * Copyright (c) 2019, cPanel, Inc.
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
 * @author    Dustin Scherer <dustin.scherer@cpanel.net>
 * @copyright Copyright (c) 2019, cPanel, Inc., All rights Reserved. (http://cpanel.net) 
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

    $HOST_NAME = '10.1.32.118';
    $HOST_USER = 'green';
    $TOKEN = '33JK2FL611YNDACZX1EO4M1N7O37HHPE';
    
    /**
     * Getting a cPanel Service object and invoking the api2_request method on
     * the cPanel Service object itself.  The preferred method is to use the
     * cpanel_api{n}_request() method available in the PublicAPI interface, but
     * this is here for demonstration purposes of the Service available in the
     * cPanel library.
     * 
     * We use the cPanel Service's accessor methods to set initialization
     * variables.  Authenticating as the user with a token
     */    
    $cpanel = Cpanel_PublicAPI::factory('cPanel');
    $cpanel->setUser($HOST_USER)
           ->setToken($TOKEN)
           ->setHost($HOST_NAME);
           
    $service = 'cpanel';
    $queryMF = array(
        'module' => 'Email',
        'function' => 'listforwards',
        'user' => $HOST_USER,
    );
    $response = $cpanel->api2_request($service, $queryMF);
    
    echo "API2 response for {$queryMF['module']}::{$queryMF['function']} '\n";

    foreach ($response->cpanelresult->data as $dataset) {
        foreach ($dataset as $key => $value) {
            echo "\t$key: $value\n";
        }
    }

    echo "\n";
}
CATCH(Exception $e) {
    echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
echo "EOF: You've successfully ended the " . basename(__FILE__) . " script.\n";
?>