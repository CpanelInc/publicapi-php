<?php
/**
 * Example usage of cPanel Service
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
     * PublicAPI client uses provides two methods for intefacing with cPanel:
     * 'cpanel_api1_request()' for API1 and 'cpanel_api2_request()' for API2.
     * 
     * cpanel_api1_request() and cpanel_api2_request() have identical parameters:
     *  * $service
     *    - The Service to inteface with. Options are:
     *      - 'whostmgr' for port 2087 (authenticating as root or reseller)
     *      - 'cpanel'   for port 2083 (authenticating as any account)
     *      - 'live'     for use of the LivePHP protocol, see cPanel_LivePHP.php
     *                   in the Example directory.
     *  * $mod_func_user_args
     *    - An array containing a 'module' and 'function' key/value pair
     *      - If 'WHM' service was select for $service, a 'user' key/value pair
     *        will also need to be provided, specifying the account to query
     *        against
     *        
     *  * $func_args
     *    - An array of arguments for the API function being called
     *      - Note: if making an API1 call, the array should be ordinal
     *              if making an API2 call, the array should be associative
     *      
     * 
     * The underlying cPanel Service object also supports method originally
     * available in the XML-API client class.  Those methods include
     * api1_query() and api2_query().
     */
    
    /**
     * PublicAPI style
     * 
     * Using the 'whostmgr' service, authenticated as root
     */
    $cpCfg = array(
        'cpanel'=>array(
            'service'=>array(
                'cpanel' => array(
                    'host' => '10.1.4.191',
                    'user' => 'root',
                    'password' => 'rootsecret',
                )
            )
        )
    );
    
    $cp = Cpanel_PublicAPI::getInstance($cpCfg);
    
    $queryMF = array(
        'module' => 'PHPINI',
        'function' => 'getoptions',
        'user' => 'dave',
    );
    $queryArgs = array(
        'dirlist' => 'allow_url_fopen',
    );
    $response = $cp->cpanel_api2_request('whostmgr', $queryMF, $queryArgs);
    
    echo "API2 response for {$queryMF['module']}::{$queryMF['function']} 'dirlist={$queryArgs['dirlist']}'\n";
    foreach ($response->cpanelresult->data as $dataset) {
        foreach ($dataset as $key => $value) {
            echo "\t$key: $value\n";
        }
    }
    echo "\n";
    
    /**
     * Same as above by with the 'cpanel' service and authenticated as the user
     */
    
    /**
     * WARNING:
     *  resetInstance() is used here so that the example functions
     *  out-of-the-box. There shoud be no reason for this in production code,
     *  since a master config will not change during the execution of any given
     *  script using the PublicAPI client.
     *  -- DO NOT use the following static function in production code! --
     */ 
    Cpanel_PublicAPI::resetInstance();
    
    $cpCfg = array(
        'cpanel'=>array(
            'service'=>array(
                'cpanel' => array(
                    'host' => '10.1.4.191',
                    'user' => 'dave',
                    'password' => 'dsecret!',
                )
            )
        )
    );
    
    $cp = Cpanel_PublicAPI::getInstance($cpCfg);
    
    $queryMF = array(
        'module' => 'PHPINI',
        'function' => 'getoptions',
    );
    $queryArgs = array(
        'dirlist' => 'allow_url_fopen',
    );
    $response = $cp->cpanel_api2_request('cpanel', $queryMF, $queryArgs);
    echo "API2 response for {$queryMF['module']}::{$queryMF['function']} 'dirlist={$queryArgs['dirlist']}'\n";
    foreach ($response->cpanelresult->data as $dataset) {
        foreach ($dataset as $key => $value) {
            echo "\t$key: $value\n";
        }
    }
    echo "\n";
    
    
    /**
     * Same as last example, only with api1
     */
    $queryMF = array(
        'module' => 'Email',
        'function' => 'getmailserver',
        'user' => 'dave',
    );
    /**
     * NOTE, this is API1, so we use an ordinal array
     *  -for this particular API1 call the 1st arg is a value representing
     *  'account'
     */
    $queryArgs = array(
        'lildave@dave.com',
    );
    echo "API1 response for {$queryMF['module']}::{$queryMF['function']} 'account={$queryArgs[0]}'\n";
    $response = $cp->cpanel_api1_request('cpanel', $queryMF, $queryArgs);
    echo "\tResult: {$response->data->result}\n\n";
    
    /**
     * WARNING:
     *  resetInstance() is used here so that the example functions
     *  out-of-the-box. There shoud be no reason for this in production code,
     *  since a master config will not change during the execution of any given
     *  script using the PublicAPI client.
     *  -- DO NOT use the following static function in production code! --
     */
    Cpanel_PublicAPI::resetInstance();
    
    
    
    /**
     * Getting a cPanel Service object and invoking the api2_request method on
     * the cPanel Service object itself.  The preferred method is to use the
     * cpanel_api{n}_request() method available in the PublicAPI interface, but
     * this is here for demonstration purposes of the Service available in the
     * cPanel library.
     * 
     * We use the cPanel Service's accessor methods to set initialization
     * variables.  Authenticating as the user. 
     */    
    $cpanel = Cpanel_PublicAPI::factory('cPanel');
    $cpanel->setUser('dave')
           ->setPassword('dsecret!')
           ->setHost('10.1.4.191');
           
    $service = 'cpanel';
    $queryMF = array(
        'module' => 'PHPINI',
        'function' => 'getoptions',
        'user' => 'dave',
    );
    $queryArgs = array(
        'dirlist' => 'allow_url_fopen',
    );
    $response = $cpanel->api2_request($service, $queryMF, $queryArgs);
    
    echo "API2 response for {$queryMF['module']}::{$queryMF['function']} 'dirlist={$queryArgs['dirlist']}'\n";
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