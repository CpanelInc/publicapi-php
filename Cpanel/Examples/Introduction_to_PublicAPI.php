<?php
/**
 * Introduction to PublicAPI and the cPanel PHP library
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
TRY {
    /**
     * Introduction to the PublicAPI Client Object and Services
     * 
     * The PHP PublicAPI client is a wrapper around the cPanel PHP library.  It
     * provides the programmer with an easy way to create Service objects and 
     * despatch the PublicAPI methods to them.
     * 
     * A Service object represents a set of defined interfaces for communicating
     * with cPanel systems.  For example the WHM Service uses the XML-API
     * interface for making remote calls to a cPanel server on ports 2087 or
     * 2086.  Because it honors the XML-API interface there are methods for
     * invoking native XML-API functions as well as cPanel API1 and API2 modules
     * and functions.  A cPanel Service would do the same, but on ports 2083 or
     * 2082.
     * 
     * A programmer can instantiate a Service object at any time, and without
     * using the PublicAPI client. (remember it's a library!).  However, as your
     * application and scripts get more complex, the PublicAPI client object may
     * be a better way of spawn Service objects.  The PublicAPI client is a 
     * singleton. The main purpose of this is it allow for an internal registry.
     * When Service objects are spawned, the PubicAPI client stores that object 
     * internally.  The object can be accessed at any point during a script's
     * execution, allowing the programmer to have multiple Service instances of 
     * the same type, all with (potentially) unique configurations.
     * 
     * A quick note: The WHM, and the cPanel, Service objects were designed to
     * be backwards compatible with the XML-API client class. Most scripts that 
     * used that class should be able to function will little modification,
     * albeit mostly concerning instantiation and initialization of variables.
     * Also, the cPanel Service object was designed to be backwards compatible
     * with the LivePHP class that ships with the cPanel & WHM product.  Again,
     * there's a high level of compatibility concerning methods calls, however
     * there are a few caveats, namely the cpanelprint() method's return. 
     * 
     * 
     * Using the PublicAPI Client
     * 
     * First, include the Autoload.php file located in Cpanel/Util/.  This is a
     * simply autoload script that registers an __autoload function with the
     * SplAutoload stack.  It will also ensure that the Cpanel/ directory is in
     * the include_path.  These two elements are necessary for the library to 
     * function properly, though you are free to implement them in any way you
     * see fit.  Autoload.php is there for your convenience.
     */
    
    require_once realpath( dirname(__FILE__) . '/../Util/Autoload.php');
    
    /**
     * To instantiate your PublicAPI client, call 
     * Cpanel_PublicAPI::getInstance(). 
     */
    
    $cp = Cpanel_PublicAPI::getInstance();

    /**
     * Getting a cPanel & WHM Service
     * 
     * You can get a specific cPanel & WHM services via the factory method
     * OR you can instantiate the service directly from a service class
     * 
     * In either case you'll want to pass in some initialization data, here's a
     * basic config array
     */
    
    $whmCfg = array(
        'host' => '10.1.4.191',
        'user' => 'root',
        'password' => 'rootsecret'
    );
    
    /**
     * The following lines of code illustrate both ways of obtaining a Service
     * object (mentioned earlier)
     * 
     * The first example illustrates using the PublicAPI factory method as a
     * static call.  Alteratively, you can invoke factory() from the PublicAPI
     * object returned above.
     */ 
     
    $xmlapi = Cpanel_PublicAPI::factory('WHM', $whmCfg);
    $xmlapi = $cp->factory('WHM', $whmCfg);
    
    /**
     * This next example illustrates using the cPanel library directly.
     * 
     * Note: this method does NOT register the Service object in the PublicAPI
     * registry, use the above methods if that is the desired affect
     */
    
    $xmlapi = new Cpanel_Service_WHM($whmCfg);
    
    /**
     * If you did not pass a configuration earlier, wish to change on of those
     * values or wish to change a default value for the Service, you can use
     * various accessor methods.  Any value can be set by calling 
     * "setKey(value)".  Additionally,  most of the accessors available in the
     * legacy XML-API client class are available too.
     * 
     * This example illustrates defining the port, both native accessor and the
     * legacy method from the XML-API class
     */
    
    $xmlapi->setPort('2087');
    $xmlapi->set_port('2087');
    
    /**
     * Once your Service object is ready, you can invoke the methods available
     * for that service.
     * 
     * This example illustrates fetching the cPanel & WHM version from the
     * remote server
     */
    $response = $xmlapi->version();
    
    /**
     * Any request methods against a Service or PublicAPI method returns a
     * Cpanel_Query_Object. Cpanel_Query_Object is a sophisticated container 
     * responsible for managing the query information as well as the response
     * information.
     * 
     * This "response" object that is returned provides a simple object
     * oriented interface that allows you to source data, moving deeper into 
     * the data structure using the little arrow ("->"), just like any other
     * nested PHP object.
     * 
     * Since our previous request method simply fetched the cPanel version, all
     * that is necessary to retrieve that data from the 
     */

    echo "WHM Version: {$response->version}\n";
    
    /**
     * Using the PublicAPI Client Interface
     * 
     * The above examples and documentation describe the very basics of
     * PublicAPI, albeit geared towards developers familar to the XML-API client
     * class.  However, the PublicAPI client is designed to honor to the
     * conventions of it's sibling Perl client Cpanel::PublicAPI which offers
     * a specific, new set of query methods.
     * 
     * This methods are:
     *  * whm_api($function $func_args)
     *    - For WHM (native XML-API) functions against the WHM Service
     *    
     *  * cpanel_api1_query($service, $mod_func_user_args, $func_args)
     *    - For API1 queries against cPanel or WHM Service
     * 
     *  * cpanel_api2_query($service, $mod_func_user_args, $func_args)
     *    - For API2 queries against cPanel or WHM Service
     *    
     *  api_request($service, $url, $HTTP_REQUEST_TYPE, $formdata, $customHeaders );
     *    - For making a direct URL query against a cPane or WHM Service UI
     *    
     * All of these methods are supported directly in the PublicAPI client.
     * 
     * An example (in it's entirety, and shortest notation) of the previous
     * "version" request is as follows:
     */
    
    require_once realpath( dirname(__FILE__) . '/../Util/Autoload.php');
    $cpCfg = array(
        'cpanel'=>array(
            'service'=>array(
                'whm' => array(
                    'host' => '10.1.4.191',
                    'user' => 'root',
                    'password' => 'rootsecret',
                )
            )
        )
    );
    $cp = Cpanel_PublicAPI::getInstance($cpCfg);
    $response = $cp->whm_api('version');
    echo "WHM Version: {$response->version}\n";
}
CATCH(Exception $e) {
    echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
echo "EOF: You've successfully ended the " . basename(__FILE__) . " script.\n";
?>