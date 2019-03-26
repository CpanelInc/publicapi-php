<?php
/**
 * Example usage of configuration options for the PublicAPI client and cPanel
 * PHP library.
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
     * The PublicAPI client and Service take an optional array of configuration
     * data.  The PublicAPI client is a singleton object and is initialized by
     * calling the getInstance() method.  An the first invocation, an array of
     * configuration data can be passed.  For Service objects, simply pass the
     * array to the construct when instantiating the new object.
     * 
     * First, will go through a Service example.
     * 
     * The following illustrates use the PublicAPI::factory() method
     */
    
    $config = array(
        'host' => '10.1.4.191',
        'user' => 'root',
        'password' => 'rootsecret',
    );
    $whm = Cpanel_PublicAPI::factory('WHM', $config);
    $response = $whm->xmlapi_query('version');
    echo "WHM Version: {$response->version}\n\n";
    
    /**
     * A Service can be instantiated directly from the library
     */
    $whm = new Cpanel_Service_WHM($config);
    $response = $whm->xmlapi_query('version');
    echo "WHM Version: {$response->version}\n\n";
    
    /**
     * Reseting the PubicAPI internals for example to function cleanly
     * WARNING:
     *  resetInstance() is used here so that the example functions
     *  out-of-the-box. There shoud be no reason for this in production code,
     *  since a master config will not change during the execution of any given
     *  script using the PublicAPI client.
     *  -- DO NOT use the following static function in production code! --
     */ 
    Cpanel_PublicAPI::resetInstance();
    
    /**
     * PublicAPI's global configuration array
     * 
     * The global configuration array, that is passed on the first call to
     * PublicAPI::getInstance(), consists of two namespaces:
     *  * package
     *    - Response for the internal behavior of the PublicAPI client itself
     *  * service
     *    - Contains configuration for Service objects
     *    
     * So, the basic structure of a global configuration array would be:
     * <?php
     * $cfg = array(
     *     'cpanel' => array(
     *         'package'=> array(),  // An array for PublicAPI behavior
     *         'service'=>array()    // An array for Service Object config data
     *     ),
     * );
     * ?>
     * 
     * NOTE: In the above structure, we nest those namespaces within a parent
     * namesponse called 'cpanel'.  This is not a requirement; if the 'cpanel'
     * key is not found in the first level of the configuration array passed,
     * the PublicAPI client will assume all keys (namely 'package' and 'service'
     * ) are relevant and will be stored for potential retrieval later.
     * 
     * This 'cpanel' namesponse is a convenience for developers that what to use
     * a configuration that contains data for other parts of their application.
     * 
     * A good use case would be the inclusion of PublicAPI into a Zend Framework
     * application, which would likely already be using a Zend_Config object.
     * The developer could invoke the toArray() method on their Zend_Config
     * object and pass that into getInstance();
     */
    
    /**
     * PublicAPI's 'package' namespace and the internal registry
     * 
     * When using the PublicAPI client, an interal registry is created by
     * default.  This registry is used by the factory() method for storing and
     * retieving Service objects instantiated by the PublicAPI interface.
     * 
     * In the event that you wish to disable the registry, or have it use a
     * custom class, the 'package' namesponse of the global config can be
     * directed as follows:
     */
    
    /**
     * Disable the registry entirely
     */
    $pkgOpts = array(
        'registryClass'   => 'disabled',
        
    );
    $masterConfig = array(
        'package' => $pkgOpts,
    );
    
    /**
     * Use a custom class.  Custom registry classes must extend ArrayObject
     */
    $pkgOpts = array(
        'registryClass'   => 'Zend/Registry.php',
        
    );
    $masterConfig = array(
        'package' => $pkgOpts,
    );
    
    /**
     * PublicAPI's 'service' namespace
     * 
     * The first level of the service config array should specify a service
     * type.  i.e., 'whm' or 'cpanel'.  The next descendent level can contain
     * either a 'config' namespace (for a generic set of config data) or a
     * unique identifier for a set of configs.
     * 
     * In the following example, we have two unique namespaces and one generic:
     */
    $services = array(
        'whm' => array(
            'myserver1' => array(
                'config' => array(
                    'host' => '10.1.4.191',
                    'user' => 'root',
                    'password' => 'rootsecret'
                ),
            ),
            'myserver2' => array(
                'host' => '10.1.4.102',
                'user' => 'root',
                'password' => 'rootsecret'
            ),
            'config'    => array(
                'host' => '10.1.4.191',
                'user' => 'root',
                'password' => 'rootsecret'
            ),
        ),
    );
    
    $masterConfig = array(
        'service' => $services,
    );
    /**
     * NOTE: for the unique WHM service 'myserver1', the configuration data is
     * nested in a 'config' namespace.  This is currently optional, however
     * it is encouraged for anyone wanting to extend the cPanel Service library
     * component.  This is a logical grouping to separate the Service config
     * data from behavior directives
     */
    
    /**
     * Using a named Service configuration
     * 
     * You can get a specific cPanel & WHM services via the factory method.
     * The third argument of the PublicAPI::factory() method is used to specify
     * a particular "name" for a Service instance.
     * 
     * If a matching unique identifier ("name") is found in the global config,
     * that config data will be passed to the Service's constructor upon
     * instantiation.
     * 
     * If a match is not found, any generic data available in the global config
     * will be passed to the Service constructor
     * 
     * If a match is not found, generic data is available AND configuration data
     * was passed in the second parameter to factory(), the passed in data will
     * merge on top of the generic data.  The merged data will be passed to the
     * Service constructor.
     * 
     * After a Service object is instantiated with the factory() method, it will
     * be stored into the PublicAPI registry (if the registry was not previously
     * disabled).  This Service object will be stored with the name 'default' if
     * a name was not provided (in the third parameter of the factory() call)
     */
    $cp = Cpanel_PublicAPI::getInstance($masterConfig);
    
    /**
     * Retrieve the Whostmgr Service named 'myserver1', instantiating it as
     * necesssary
     */
    $my1_whm = Cpanel_PublicAPI::factory('WHM', '', 'myserver1');
    $response = $my1_whm->version();
    echo "WHM Version for 'myserver1' via named config: {$response->version}\n\n";
    
    $my2_whm = Cpanel_PublicAPI::factory('WHM', '', 'myserver2');
    $response = $my2_whm->version();
    echo "WHM Version for 'myserver2' via named config: {$response->version}\n\n";
    
    $default_whm = Cpanel_PublicAPI::factory('WHM');
    $response = $default_whm->version();
    echo "WHM Version for unnamed config data associated with WHM Services: {$response->version}\n\n";

    
    /**
     * Just as a side note and example.  Any Service level config can be passed
     * to the constructor of a Service_* library class.  This method of
     * instantiation will not store the new object into a PublicAPI registry.
     */
    $whmCfg = $services['whm']['myserver2'];
    $xmlapi2 = new Cpanel_Service_WHM($whmCfg);
    $response = $xmlapi2->version();
    echo "WHM Version for 'myserver2' via direct init: {$response->version}\n\n";
    
    
}
CATCH(Exception $e) {
    echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
echo "EOF: You've successfully ended the " . basename(__FILE__) . " script.\n";
?>