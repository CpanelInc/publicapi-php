<?php
/**
 * Example usage of LivePHP Service
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
     * The follow function prepares an environment that emulates cpaneld that is
     * present when loading *.live.php scripts (e.g., the LivePHP environment).
     * This is ONLY FOR DEMO PURPOSES.  CAVEAT EMPTOR: The provided mock server
     * is for use by this example script only.  It is not supported in any way
     * by the cPanel PHP Library, or the PublicAPI project.
     */
    startExampleMockServer();
    
    
    ////////////////////////////////////////////////////////////////////////////
    // Prepare PublicAPI and provision a LivePHP Service object 
    $cp = Cpanel_PublicAPI::getInstance();
    $cpanel = Cpanel_PublicAPI::factory('LivePHP');
    
    /**
     * Note about the LivePHP Service
     * 
     * The LivePHP Service is intended for use on a cPanel system in accordance
     * with the LivePHP environment.  It has not meaning outside that context.
     * 
     * Pleas see the cPanel documentation for more information on LivePHP
     * http://docs.cpanel.net/twiki/bin/view/AllDocumentation/AutomationIntegration/LivePHP
     * 
     * 
     * Technical Detail and Caveat:
     * The LivePHP Service is actually a cPanel Service object with a special
     * backend adapter, Cpanel_Service_Adapter_Liveapi.  This type of
     * inheritance allow for legacy methods of LivePHP to be available while
     * also providing access for the respective PublicAPI interface methods,
     * namely cpanel_api1_request and cpanel_api2_request.
     * 
     * This also means that if your script lives on a cPanel system and is
     * executed under the LivePHP environment, AND your code instantiates a
     * cPanel Service object (as 'cpanel' and not 'livephp' with the PubliAPI::
     * factory() or direct Service creating via the library classes), that
     * Service object will become optimized and use the LivePHP adapter instead 
     * of the default.
     */
    
    /**
     * Legacy LivePHP examples
     * 
     * NOTE: The PublicAPI client and Service objects always return a
     * Cpanel_Query_Object.  The original LivePHP class would return a string
     * (the value of the "result" node within the LiveJSON response format) for
     * the various methods starting with "cpanel", such as "cpanelprint".  
     * 
     * In order for this behavior to be observed, you must set the Legacy mode.
     * Legacy mode is only available, and only has meaning, for the LivePHP
     * Service.
     * 
     * Setting Legacy mode, an how that affects the returned value from the
     * "cpanel*" method being invoked, is illustrated below. 
     */
    
    // exec()
    echo "\n\nLivePHP style 'exec' method for code string: '<cpanel print=\"foo\">'\n";
    $response = $cpanel->exec('<cpanel print="foo">');
    echo "\t{$response->cpanelresult->data->result}\n";
    
    // api1()
    echo "\n\nLivePHP style 'api1' method for module='print' with argument 'foo'" 
        . "\n[remember API1 takes ordinal array like 'array(\"foo\")']\n";
    $response = $cpanel->api1('print', '', array('foo'));
    echo "\t{$response->cpanelresult->data->result}\n";
    
    // fetch cpvar
    echo "\n\nLivePHP style 'fetch' method with argument '\$homedir'\n" 
        . "[same as previous example, only using the fetch() method]\n";
    $response = $cpanel->fetch('$homedir');
    echo "\t{$response->cpanelresult->data->result}\n";

    // cpanelprint
    echo "\n\nLivePHP style 'cpanelprint' method with argument 'foo'\n";
    $response = $cpanel->cpanelprint('foo');
    echo "\t{$response->cpanelresult->data->result}\n";
    
    // cpanelprint cpvar
    echo "\n\nLivePHP style 'cpanelprint' method with argument '\$homedir'\n"
        . "[cpanelprint() and fetch(), which use an API1 print cptag, can get user environment vars aka cPvars]\n";
    $response = $cpanel->cpanelprint('$homedir');
    echo "\t{$response->cpanelresult->data->result}\n";
    
    // cpanelprint, this time we'll set the Legacy mode for our $cpanel Service
    // object prior to invocation
    $cpanel->setAdapterMode(Cpanel_Service_Abstract::ADAPTER_LIVE, Cpanel_Service_Adapter_Liveapi::LEGACY_STRING_MODE);
    echo "\n\nLivePHP style 'cpanelprint' method with argument '\$homedir'\n"
        . "[The Service object (under the hood) is now in Legacy Mode!]\n";
    $response = $cpanel->cpanelprint('$homedir');
    echo "\t{$response}\n";

    // cpanelif
    echo "\n\nLivePHP style 'cpanelif' method with argument '\$haspostgres'\n"
        ."[Still in Legacy Mode!]\n";
    $response = $cpanel->cpanelif('!$haspostgres');
    echo "\t{$response}\n";
    
    // Undo Legacy mode
    $cpanel->unsetAdapterMode(Cpanel_Service_Abstract::ADAPTER_LIVE, Cpanel_Service_Adapter_Liveapi::LEGACY_STRING_MODE);
    // cpanelfeature
    echo "\n\nLivePHP style 'cpanelfeature' method with argument 'fileman'\n"
        ."[Legacy Mode has been turned off.]\n";
    $response = $cpanel->cpanelfeature('fileman');
    echo "\t{$response->cpanelresult->data->result}\n";

    
    /**
     * PublicAPI native query methods are available too.
     * 
     * This example illustrates the API2 method for PublicAPI, where a 'Live'
     * Service is requested
     */
    
    $cp = Cpanel_PublicAPI::getInstance();
    // Must have an array that defines what API module and function to call
    $queryMF = array(
        'module' => 'PHPINI',
        'function' => 'getoptions',
    );
    
    // Also need an array of arguments related to the API function being requested 
    $queryArgs = array(
        'dirlist' => 'allow_url_fopen',
    );
    
    echo "\nAPI2 response for {$queryMF['module']}::{$queryMF['function']} 'dirlist={$queryArgs['dirlist']}'\n";
    $response = $cp->cpanel_api2_request('Live', $queryMF, $queryArgs);
   
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



/**
 * Utility function for executing the mockserver script
 * 
 * This allow this sample script to be executed, and act as if it were a real
 * *.live.php script living on a cPanel server.
 * 
 * @throws Exception If the mock server script cannot be located
 * @throws Exception If a mock server fails to start
 * @throws Exception If the mock server does not create the socket file
 */
function startExampleMockServer()
{
    $n = rand(10e10, 10e14);
    $rand = base_convert($n, 10, 36);
    $socketfile = "/tmp/php-connector-{$rand}.sock";
    putenv("CPANEL_PHPCONNECT_SOCKET={$socketfile}");
    //    self::$socketfile = $socketfile;
    $dir = dirname(__FILE__);
    $script = 'startMockSocketServer.php';
    $mockserverscript = realpath( CPANEL_LIB_PATH . "/Tests/{$script}");
    if (!file_exists($mockserverscript)) {
        throw new Exception("Mock socket server script '$mockserverscript' does not exist");
    }
    $cmd = "/usr/bin/php -f $mockserverscript";
    $arg = "socketfile={$socketfile}";
    $full_cmd = "nohup $cmd $arg > /dev/null 2>&1 & echo $!";
    echo "\n\n== Preparing Mock Server ==\n     = Please wait = \n";
    $PID = exec($full_cmd);
    $mockSocketServerPID = $PID;
    $lookup = exec("ps -p {$PID} | grep -v 'PID'");
    sleep(2);
    if (empty($lookup)) {
        throw new Exception('Failed to start mock socket server');
    } elseif (!file_exists($socketfile)) {
        throw new Exception('Socket file does not exist: ' . $socketfile);
    }
    echo "== Mock Server Ready ==\n\n";
}
?>