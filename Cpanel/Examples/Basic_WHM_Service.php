<?php
/**
 * Example usage of WHM Service
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
     * PublicAPI style
     */
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
    
    /**
     * One alternative style
     */
    $cp = Cpanel_PublicAPI::getInstance();
    $whm = $cp->factory('WHM');
    $whm->setUser('root')
        ->setPassword('rootsecret')
        ->setHost('10.1.4.191');
    $response = $whm->xmlapi_query('version');
    echo "WHM Version: {$response->version}\n";
    
    /**
     * Another alternative is to pass the config to factory() method
     */
    $config = array(
        'host' => '10.1.4.191',
        'user' => 'root',
        'password' => 'rootsecret',
    );
    $whm = $cp->factory('WHM');
    $response = $whm->xmlapi_query('version');
    echo "WHM Version: {$response->version}\n";
    
    /**
     * Using direct library
     */
    $config = array(
        'host' => '10.1.4.191',
        'user' => 'root',
        'password' => 'rootsecret',
    );
    $whm = new Cpanel_Service_WHM($config);
    $response = $whm->xmlapi_query('version');
    echo "WHM Version: {$response->version}\n";
}
CATCH(Exception $e) {
    echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
echo "EOF: You've successfully ended the " . basename(__FILE__) . " script.\n";
?>